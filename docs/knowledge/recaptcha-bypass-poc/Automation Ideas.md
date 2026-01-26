# Automation Ideas for Lead Form Testing

1. The "Golden Ticket" Approach
   Use a secure "Test Mode" Cookie or HTTP Header to disable ReCaptcha and tag submissions as TEST.

2. The "Direct Line"
   Skip the UI and test via a protected API endpoint that simulates the form handler.

3. The "Dry Run"
   Add a dry_run parameter to submissions that processes the lead but stops before sending to the CRM.

4. The "Config Monitor"
   Verify the configuration (IDs, emails) in the HTML without submitting the form.

5. The "Whitelisted IP"
   Whitelist the testing bot's IP address to bypass ReCaptcha.
