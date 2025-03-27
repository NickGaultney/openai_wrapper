### Step 1
In this folder, I would like you to scaffold a new Wordpress plugin that acts as an openAI API wrapper. The name of the plugin should be OpenAI Wrapper.

The plugin should have the following structure:
- assets/
    - styles.css
    - scripts.js
- includes/
- openai-wrapper.php
- readme.txt

Where openai-wrapper.php is the main plugin file and only initializes any files needed. All of the functionality should be in the includes folder in files named based on the feature they are responsible for. That way adding new features is as easy as adding a new file to the includes folder and updating the openai-wrapper.php file to initialize the new feature.

I have already created the root plugin folder, and we are currently in it, so don't worry about that.

This plugin should:
    - add a shortcode called [openai-wrapper].
    - Add a settings page under the Settings menu called OpenAI Wrapper that adds inputs for:
        - OpenAI API Key
        - Model type
        - Assistant ID
        - etc
    - When the shortcode is used, it should display a full openAI chat interface with a text input and a submit button. Just like ChatGPT. The users messages and ai responses should be displayed in a chat log for easy reference. So long as the user is on the same page, the conversation should be maintained using an assistant thread. If the user refreshed the page, the conversation should be started new.

Here are a few more details:
    - My name is Nick Gaultney (use this where appropriate)
    - The version of this plugin should be 0.1.0
    - Write the plugin using PHP and vanilla Javascript if possible 
    - implement nonce verification and data sanitization for the settings page and AJAX calls?
    - Create custom REST API endpoints for the chat functionality, instead of using admin-ajax.php
    - Errors should be displayed on the frontend to the user as a notice banner.
    - No need to store conversation history on the server side, just use the assistant thread id.
    - The style should be modern and clean, modeling the chatGPT interface (dark theme).
    - For model types, I would like to have the following options available as a dropdown:
        - gpt-4o
        - gpt-4o-mini
        - gpt-o1
        - gpt-o1-mini
        
