# Zappli CRM Elementor Integration

Integrates Elementor forms with Zappli CRM to seamlessly capture form submissions and manage customer interactions directly within your CRM.

## Description

Zappli CRM Elementor Integration allows you to connect Elementor form submissions to your Zappli CRM, enabling you to automate the data capture process and streamline your workflow. This plugin is perfect for WordPress users seeking efficient CRM integration to enhance their customer relationship management capabilities directly from their website.

## Features

- **Seamless Integration**: Connects directly with Zappli CRM using API tokens.
- **Customizable CRM Fields**: Map Elementor form fields to CRM fields.
- **Redirection Options**: Redirect users after submission based on the response from CRM.
- **Session Tracking**: Captures and tracks campaign parameters for better analytics.

## Installation

1. **Upload the Plugin Files**:
    - Download the plugin.
    - Navigate to your WordPress dashboard, go to `Plugins` > `Add New` > `Upload Plugin`.
    - Upload the zipped file of the plugin and click on `Install Now`.

2. **Activate the Plugin**:
    - After installation, click on `Activate Plugin` to start using it.

## Usage

Once installed and activated, navigate to any Elementor form:

1. Add a new "Action After Submit" and choose "Zappli CRM Submit".
2. Expand the new "Zappli CRM" section and configure the CRM URL, API token and other settings as required.
    - **CRM URL**: Your Zappli CRM base URL without the trailing slash (e.g., `https://crm.zappli.com.au`).
    - **API Token**: API token provided by Zappli CRM for authentication.
    - **Redirect Option**: Choose between "None", "URL" and "CRM":
        - **None**: No redirection after form submission.
        - **URL**: Specify a URL to redirect users to after form submission.
        - **CRM**: Redirect users to the a page provided by the CRM (e.g. customer portal).
    - **Extra Fields**: Additional fields you may want to pass to CRM. Specify the field names and values as url-encoded key-value pairs.
        - Example: `source=google&tags=tag1,tag2`.


## Troubleshooting

- **Form not submitting**: Check that the API token and CRM URL are correctly configured. Also, ensure that your server is able to reach the CRM's API endpoint.
- **Data not appearing in CRM**: Verify that the field mappings are correctly set up and that the CRM endpoint is correctly configured to receive data.

## Support

For support, please contact [support@zappli.com.au](mailto:support@zappli.com.au).

## License

This plugin is licensed under the GPL2. See the LICENSE file for details.

## Author

Andrej Kudriavcev
