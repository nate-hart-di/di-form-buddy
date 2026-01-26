## **ISSUE: THE SYNC IN THE DI DASHBOARD FAILED**

- The Configuration progress bar in the DI Dashboard may take a while to load. Sometimes the progress bar will turn red and say failed.
- If your sync fails try it again, if it fails again post in the Content Dev slack channel and see if any other devs are experiencing issues.
  - _If they are one of you will submit an Engineering ticket with the details of the issues you’re experiencing...please include a screenshot so they can troubleshoot._

---

## **ISSUE: THE FORM BUILD FAILED IN THE DI DASHBOARD**

- Make sure you’re using the correct slug.
- Try running the customized build again.
- If it fails again post in the Content Dev slack channel and see if any other devs are experiencing issues.
  - _If they are one of you will submit an Engineering ticket with the details of the issues you’re experiencing...please include a screenshot so they can troubleshoot._

---

## **ISSUE: THERE ARE NO ADF OR TEXT FORMATTED NOTIFICATIONS ON MY DEV SITE**

**Confirm all of the questions below:**

- Did you enter the emails in the appropriate fields?
- Did you save your changes in the dashboard?
- Did you sync forms in the dashboard?
- Did the sync actually go through?
- Did you build forms in bamboo?
- Did your form build actually go through? 

If all of the above was yes then post in the Content Dev slack channel for a second pair of eyes!

---

## **ISSUE: THE DEALER DID NOT RECEIVE THE TEST LEADS INTO THEIR CRM (NOT USING SHIFT)**

1. What form(s) are not receiving leads?
2. Co
   <truncated 3070 bytes>
   of the page.

**SOLUTION:**

Activate the ajax.  
Classic: Update shortcode: `[gravityform id="3" name="Contact Us" title="false" description="false" ajax="true"]`  
SB widget:

![](blob:https://media.staging.atl-paas.net/?type=file&localId=null&id=04e95797-094b-4d3c-84b6-608de5ad15b0&&collection=contentId-531890867&height=1260&occurrenceKey=null&width=1588&__contextId=null&__displayType=null&__external=false&__fileMimeType=null&__fileName=null&__fileSize=null&__mediaTraceId=null&url=null)

---

## **ISSUE: VIN SOLUTIONS CRM**

You cannot send leads to multiple vin solutions CRM's

- EX: [leads@knightdodge.co](mailto:leads@knightdodge.co)[,leads@knightnissan.co](https://dealerinspire.atlassian.net/wiki/spaces/CD/pages/620331179/Vin+Solutions#) will be rejected from both CRMs.

---

## **ISSUE: RECAPTCHA ERROR MESSAGE**

- You are getting this error message when you try to submit a form: “There is an error with the reCAPTCHA. Please try again.”
- Try deactivating and reactivating the **Gravity Forms No CAPTCHA reCAPTCHA** plugin

![](blob:https://media.staging.atl-paas.net/?type=file&localId=null&id=d5c2c94d-5a99-43f7-8b8c-81486b492f59&&collection=contentId-531890867&height=262&occurrenceKey=null&width=1080&__contextId=null&__displayType=null&__external=false&__fileMimeType=null&__fileName=null&__fileSize=null&__mediaTraceId=null&url=null)

---

## **ISSUE:** `Spam Reason: because of numeric characters in name field`

- This error comes up if there is a number in a name field
- It's ANY field with "Name" in the title, ex “Part Name”

I gave QC a heads up on this for testing. We can also change Part Name to Part on the form field itself.

![](blob:https://media.staging.atl-paas.net/?type=file&localId=null&id=dc6f3c41-0874-4a52-b7c8-528a039a0993&&collection=contentId-531890867&height=384&occurrenceKey=null&width=931&__contextId=null&__displayType=null&__external=false&__fileMimeType=null&__fileName=null&__fileSize=null&__mediaTraceId=null&url=null)
