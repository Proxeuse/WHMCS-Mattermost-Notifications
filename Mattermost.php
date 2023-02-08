<?php

namespace WHMCS\Module\Notification\Mattermost;

require __DIR__ . '/vendor/autoload.php';

use WHMCS\Module\Notification\DescriptionTrait;
use WHMCS\Module\Contracts\NotificationModuleInterface;
use WHMCS\Notification\Contracts\NotificationInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;
use GuzzleHttp\Psr7;

/**
 * Mattermost Notification Provider
 * 
 * This is the Mattermost Notification Provider for use in WHMCS. It implements
 * the WHMCS NotificationModuleInterface and therefore uses the default WHMCS
 * notification configuration.
 * 
 * @author      Proxeuse <development@proxeuse.com>
 * @copyright   Copyright (C) 2023 Proxeuse
 * @see         https://github.com/Proxeuse/whmcs-mattermost-notifications      GitHub Page and Documentation
 * @see         https://proxeuse.com/software/whmcs/mattermost-notifications    Product Page
 * @version     1.0.0
 */

/**
 * @license GNU General Public License v3.0
 * 
 * WHMCS Mattermost Notification Provider
 * Copyright (C) 2023 Proxeuse
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class Mattermost implements NotificationModuleInterface
{
    use DescriptionTrait;

    /**
     * Constructor
     *
     * Any instance of a notification module should have the display name and
     * logo filename at the ready.  Therefore it is recommend to ensure these
     * values are set during object instantiation.
     *
     * The sample notification module utilizes the DescriptionTrait which
     * provides methods to fulfill this requirement.
     *
     * @see \WHMCS\Module\Notification\DescriptionTrait::setDisplayName()
     * @see \WHMCS\Module\Notification\DescriptionTrait::setLogoFileName()
     */
    public function __construct()
    {
        $this->setDisplayName('Mattermost')
            ->setLogoFileName('logo.png');
    }

    /**
     * Settings required for module configuration
     *
     * The method should provide a description of common settings required
     * for the notification module to function.
     *
     * For example, if the module connects to a remote messaging service this
     * might be username and password or OAuth token fields required to
     * authenticate to that service.
     *
     * This is used to build a form in the UI.  The values submitted by the
     * admin based on the form will be validated prior to save.
     * @see testConnection()
     *
     * The return value should be an array structured like other WHMCS modules.
     * @link https://developers.whmcs.com/payment-gateways/configuration/
     *
     * @return array
     */
    public function settings()
    {
        return [
            'mattermost_url' => [
                'FriendlyName' => 'Mattermost Domain',
                'Type' => 'text',
                'Description' => 'Supply your Mattermost domain name without protocols or backslashes.',
            ],
            'mattermost_bot_username' => [
                'FriendlyName' => 'Mattermost Bot Username',
                'Type' => 'text',
                'Placeholder' => 'whmcs',
                'Description' => 'Enter the bot username. In most cases this should be whmcs.',
            ],
            'mattermost_bot_access_token' => [
                'FriendlyName' => 'Mattermost Bot Access Token',
                'Type' => 'text',
                'Description' => 'Can be generated in the Integrations tab of Mattermost',
            ],
        ];
    }

    /**
     * Validate settings for notification module
     *
     * This method will be invoked prior to saving any settings via the UI.
     *
     * Leverage this method to verify authentication and/or authorization when
     * the notification service requires a remote connection.
     *
     * In the event of failure, throw an exception. The exception will be displayed
     * to the user.
     *
     * @param array $settings
     *
     * @throws \Exception
     */
    public function testConnection($settings)
    {
        // Check to ensure both mattermost_url, mattermost_bot_username and mattermost_bot_access_token were provided
        if (empty($settings['mattermost_url']) || empty($settings['mattermost_bot_username']) || empty($settings['mattermost_bot_access_token'])) {
            throw new \Exception('Please provide both the Mattermost Domain Name as well as the Mattermost Bot Access Token.');
        }

        // Retrieve the Bot object on the Mattermost server to validate an authenticated and authorized connection
        try {
            (new Client([
                'base_uri' => 'https://' . $settings['mattermost_url'] . '/api/v4/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $settings['mattermost_bot_access_token'],
                ]
            ]))->request('GET', 'users/username/'.$settings['mattermost_bot_username']);
        } catch (GuzzleException\ClientException $e) {
            throw new \Exception("An API error has occured. Response: <pre>" . $e->getResponse()->getBody()->getContents() . "</pre>");
        } catch (GuzzleException\ConnectException $e) {
            throw new \Exception("A network error has occured. Please check the domain name for correctness and perform a server reachability check using cURL.");
        } catch (GuzzleException\ServerException $e) {
            throw new \Exception("An error occurred on the Mattermost server or on the reverse proxy if used. Request: <pre>" . Psr7\Message::toString($e->getRequest()) . "</pre>");
        } catch (GuzzleException\TooManyRedirectsException $e) {
            throw new \Exception("Too many redirects occur when trying to connect to the API.");
        }
    }

    /**
     * The individual customisable settings for a notification.
     *
     * These settings are provided to the user whilst configuring individual notification rules.
     *
     * The "Type" of a setting can be text, password, yesno, dropdown, radio, textarea and dynamic.
     *
     * @see getDynamicField for how to obtain dynamic values
     *
     * @return array
     */
    public function notificationSettings()
    {
        return [
            'channelId' => [
                'FriendlyName' => 'Channel',
                'Type' => 'dynamic',
                'Description' => 'Select the desired channel for notification delivery.',
                'Required' => true,
            ],
        ];
    }

    /**
     * The option values available for a 'dynamic' Type notification setting
     *
     * @see notificationSettings()
     *
     * @param string $fieldName Notification setting field name
     * @param array $settings Settings for the module
     *
     * @return array
     */
    public function getDynamicField($fieldName, $settings)
    {        
        if ($fieldName == 'channelId') {
            try {
                // Setup the Client
                $client = new Client([
                    'base_uri' => 'https://' . $settings['mattermost_url'] . '/api/v4/',
                    'headers' => [
                        'Authorization' => 'Bearer ' . $settings['mattermost_bot_access_token'],
                    ]
                ]);

                // Get bot information
                $bot = json_decode($client->request('GET', 'users/username/'.$settings['mattermost_bot_username'])->getBody());
                // Get all channels where bot is a member of
                $response = $client->request('GET', 'users/'.$bot->id.'/channels');

                // Set empty array
                $channels = [];
                foreach (json_decode($response->getBody()) as $channel) {
                    // If the Channel has a display name and thus is a real channel and not a direct message
                    if ($channel->display_name) {
                        $channels[] = [
                            'id' => $channel->id,
                            'name' => $channel->display_name,
                            'description' => $channel->purpose,
                        ];
                    }
                }
    
                return [
                    'values' => $channels
                ];
            } catch (GuzzleException\ClientException $e) {
                throw new \Exception("An API error has occured. Response: <pre>" . $e->getResponse()->getBody()->getContents() . "</pre>");
            } catch (GuzzleException\ConnectException $e) {
                throw new \Exception("A network error has occured. Please check the domain name for correctness and perform a server reachability check using cURL.");
            } catch (GuzzleException\ServerException $e) {
                throw new \Exception("An error occurred on the Mattermost server or on the reverse proxy if used. Request: <pre>" . Psr7\Message::toString($e->getRequest()) . "</pre>");
            } catch (GuzzleException\TooManyRedirectsException $e) {
                throw new \Exception("Too many redirects occur when trying to connect to the API.");
            }
            
        }

        return [];
    }

    /**
     * Deliver notification
     *
     * This method is invoked when rule criteria are met.
     *
     * In this method, you should craft an appropriately formatted message and
     * transmit it to the messaging service.
     *
     * WHMCS provides a getAttributes method via $notification here. This method returns a NotificationAttributeInterface
     * object which allows you to obtain key data for the Notification.
     *
     * @param NotificationInterface $notification A notification to send
     * @param array $moduleSettings Configured settings of the notification module
     * @param array $notificationSettings Configured notification settings set by the triggered rule
     *
     * @throws \Exception on error of sending notification
     *
     * @see https://classdocs.whmcs.com/7.8/WHMCS/Notification/Contracts/NotificationInterface.html
     * @see https://classdocs.whmcs.com/7.8/WHMCS/Notification/Contracts/NotificationAttributeInterface.html
     */
    public function sendNotification(NotificationInterface $notification, $moduleSettings, $notificationSettings)
    {
        if (!$notificationSettings['channelId']) {
            // Abort the Notification.
            throw new \Exception('No (existing) channel selected for notification delivery.');
        }

        $notificationFields = [];
        foreach ($notification->getAttributes() as $attribute) {
            $notificationFields[] = [
                'title' => $attribute->getLabel(),
                'value' => $attribute->getValue(),
            ];
        }

        // Perform API call to your notification provider.
        try {
            // Setup the Client
            $client = new Client([
                'base_uri' => 'https://' . $moduleSettings['mattermost_url'] . '/api/v4/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $moduleSettings['mattermost_bot_access_token'],
                ]
            ]);

            $client->request('POST', 'posts', [
                'json' => [
                    'channel_id' => strtok($notificationSettings['channelId'], '|'),
                    'message' => $notification->getMessage(),
                    'props' => [
                        'attachments' => [
                            [
                                'title' => $notification->getTitle(),
                                'title_link' => $notification->getUrl(),
                                'fields' => $notificationFields,
                            ],
                        ]
                    ]
                ]
            ]);
            
        } catch (GuzzleException\ClientException $e) {
            throw new \Exception("An API error has occured. Response: <pre>" . $e->getResponse()->getBody()->getContents() . "</pre>");
        } catch (GuzzleException\ConnectException $e) {
            throw new \Exception("A network error has occured. Please check the domain name for correctness and perform a server reachability check using cURL.");
        } catch (GuzzleException\ServerException $e) {
            throw new \Exception("An error occurred on the Mattermost server or on the reverse proxy if used. Request: <pre>" . Psr7\Message::toString($e->getRequest()) . "</pre>");
        } catch (GuzzleException\TooManyRedirectsException $e) {
            throw new \Exception("Too many redirects occur when trying to connect to the API.");
        }
    }
}
