# 🔔 WHMCS Mattermost Notification Provider
Heyy 👋 This is a free and open source notification provider for Mattermost to be used in WHMCS. The module allows a bot in Mattermost to post in channels chosen by you. We have designed the module as carefully as possible and user-friendly. Just follow the instructions to install.

## 🆕 Installation
### 🗂️ Pre-Compiled Release (recommended)
1. Download the pre-compiled module release from [here](https://github.com/Proxeuse/whmcs-mattermost-notifications/releases). Make sure you download the `pre-compiled.zip` and not the `Source Code`.
2. Create a new folder called `Mattermost` in your `modules/notifications` folder on your WHMCS instance.
3. Upload the contents of the ZIP file to that Mattermost folder.

### ⚛️ Manual installation (advanced)
You should use this installation method if the pre-compiled version is not available somehow or if you don't want to use that installation type.
1. Open a SSH connection to your WHMCS server and navigate to the `/modules/notifications` folder using `cd`.
2. Clone the GitHub repository to `/modules/notifications/Mattermost` by running the following command: `git clone https://github.com/Proxeuse/WHMCS-Mattermost-Notifications Mattermost`.
3. Change directory to Mattermost and install the dependencies by `composer install`.

## ⚙️ Configuration
### 🤖 Creating a Bot in Mattermost
The first step in configuring the notification module is to create a bot in Mattermost. By using Bots and not Webhooks, we have more control over the channels WHMCS can post in. In addition, Mattermost nicely indicates that this is a message from a bot and not a message from a 'regular' user. Follow the instructions to create a bot and generate an access token.

1. Go to your Mattermost installation and navigate to the Integration settings. The settings menu is located in the upper left corner.
2. Click on `Bot Accounts` and select the `Add Bot Account` button in the upper right corner.
3. Enter an username (e.g. whmcs) and optionally fill in the other details. You do not have to select additional permissions.
4. Once you press `Create` the bot will be created and an access token will be displayed. Copy this token, you will need it in the next step of the configuration.

### 🛠️ Configuring the module in WHMCS
With the username of your new bot and the access token you have available, we can start configuring the module in WHMCS. The process will be very similar to that of other notification providers.

1. Log in to WHMCS as an admin and navigate to the Notification settings.
2. Check whether the Mattermost integration is available, otherwise make sure that you have installed it correctly. Click the `Configure` button underneath the Mattermost logo.
3. A popup will show with three fields. Now enter your Mattermost URL without any protocols or ending (back)slashes. A valid domain will look like this for instance: `mm.example.com` but not ~~https://mm.example.com~~ or ~~mm.example.com/api/v4/~~.
4. Enter the username of the bot we just created. Make sure it matches exactly, otherwise the validation will fail. Also enter the Bot Access Token which we created in the previous step.
5. Press "Save Changes" and wait for the validation to pass. If everything is configured correctly, the popup will disappear. You may proceed to create a notification rule using the Mattermost provider. If an error is thrown, please check below for troubleshooting.

### 🔔 Create a notification rule
You have successfully configured the Mattermost Notification Provider in WHMCS. You should now create a new rule, or edit an existing one, in WHMCS to instruct it to use Mattermost. Use the following steps.

1. Press the `Create New Notification Rule` button on the page you should already be at.
2. Enter a Rule name and select the event(s) for which this notification should be triggered. If you wish you can also specify certain conditions to be met. Please refer to the [WHMCS documentation](https://docs.whmcs.com/Notifications#Creating_a_Notification_Rule) for further information.
3. Select the Mattermost provider and wait for the field to propagate with the available channels. Select the desired channel for this particular notification rule and press `Create`. If a channel is not visible in WHMCS but is created in Mattermost please add the bot to the channel manually. You may check the troubleshooting documentation below.
4. Wait for the selected event to be triggered or manually trigger the event and check whether the notification is posted in the desired channel. If it is, congratulations! The module is configured correctly.

## 🐛 Troubleshooting
Oh oh! Is something not working? Let's look at it together...

### 👀 The channel is not visible in WHMCS
This is probably due to the fact that the bot has not been added in the channel in which the notification should be posted. This is normally easily solved by adding the bot as a `Member` in Mattermost. Do so by selecting the desired channel and then pressing ℹ️. The Add People button will now appear, click it. Now search for the bot you wish to add (e.g. whmcs) in the popup and click `Add`. Go back to WHMCS and check whether the channel is now visible in the list.

If it is still not visible then there is an error somewhere else. Possibly assigning extra permissions to the bot will work. To do so, go to the Integration settings in WHMCS and edit the bot.

### 🔑 An API error has occured.
This message is thrown when the Mattermost API returns an unexpected result. Usually, an incorrectly configured module is the cause of this. Check the URL, username and access token and try again. Can't figure it out? Open an Issue.

### 🔗 A network error has occured.
This error message usually occurs when a valid connection cannot be established with the Mattermost server. Check that the server is operational and, if necessary, check with a cURL command whether your WHMCS server can connect to Mattermost at all. Sometimes Firewalls or Reverse Proxies cause additional problems. Try to troubleshoot this.

An example command to run on your WHMCS server to test the connection is this:
```curl
curl --request GET --url https://mm.example.com/api/v4/system/ping
```
This command should output something in the likes of:
```json
{
  "AndroidLatestVersion": "",
  "AndroidMinVersion": "",
  "IosLatestVersion": "",
  "IosMinVersion": "",
  "status": "OK"
}
```

### 🚫 An error occurred on the Mattermost server or on the reverse proxy if used.
 This error message occurs when the Mattermost server returns an HTTP status code in the range 500-599. Using the above command, check whether the server is reachable from the WHMCS server.

### 🔁 Too many redirects occur when trying to connect to the API.
Too many redirects took place before the correct route could be reached. This is often related to a redirect loop between HTTP and HTTPS. Check the availability of Mattermost and the API via the cURL command above.

## 📝 License
WHMCS Mattermost Notification Provider
Copyright (C) 2023 Proxeuse

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
