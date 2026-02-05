**Subject**: Proposal: Automating Lead Form Testing on Live Sites

Hi Team,

Ahead of our brainstorming session, I wanted to share a concrete technical proposal to address the "Live Site Automation vs. Security" challenge.

**The Proposal: "Signed JWT Headers"**
We can implement a standard Service-to-Service authentication pattern to safely bypass ReCaptcha for our internal testing bots _only_, without lowering security for public users.

**How It Works**

1.  **The Key**: We generate a secure "digital badge" (a Signed JWT) using a private key only our team possesses.
2.  **The Handshake**: Our automated test bot includes this badge in a hidden HTTP Header (`X-DI-Test-Auth`) when submitting a form.
3.  **The Validation**: The website checks the badge.
    - **If Valid**: The system skips the ReCaptcha check and processes the lead (tagging it as "Test").
    - **If Invalid/Missing**: The system enforces standard ReCaptcha validation.

**Technical Feasibility**
This uses standard WordPress hooks (`gform_validation`) and does not require modifying core website files. It is a lightweight, low-maintenance plugin approach.

**Risk Assessment**

- **Security Risk**: **Very Low**. Access relies on a private cryptographic key, not obscurity. This is the industry standard for securing APIs (Machine-to-Machine Auth).
- **Maintenance**: **Low**. Once deployed, the logic is self-contained and requires no ongoing manual updates.

I have a Proof of Concept (POC) plugin code ready to review during our call to demonstrate the logic.

Best,

Nate
