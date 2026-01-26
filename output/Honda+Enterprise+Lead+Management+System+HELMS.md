| **Question**                                                                                                                                                                                       | **Answer**                                                  |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------- |
| **Where do these forms map through?** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                           | All New and CPO leads will route directly to Autodata/Honda |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | Yes, DI --> Autodata --> Dealer                             |

| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_ | Can’t use “Test” in any form on test leads, their system will flag the lead and mark it as spam.  
 First Name: DealerInspire Last Name: \\[dealership_name\\]1 Email: \\[dealership_name\\]1@dealerinspire.com Production environment: Any lead that has a customer email ending with @HONDATEST.com will be redirected to test dealer. This can be used by providers while testing in production end points |
| **Does this brand have any custom forms besides the core 1-16? if yes, please provide the form title, provider ID, and mapping.** _All sites go through development with the core #1-16 forms (Contact Us, Get E-Price, etc), does this brand have any specific forms that are required past these core 16? If so, what are they and how are they used on the site?_ | No |
| **Buy/Sell and forms** _How are forms tested or updated when a site goes through a buy/sell? Are live site updates allowed?_  
_When is a new dealer code activated based on the buy/sell sale?_  
_Anything else form specific to know about Buy/Sells?_ | Updated Dealer code and send test leads through, if any issues come up during testing reach out to [info@hondadigitaldealer.com](mailto:info@hondadigitaldealer.com) for assistance. |
| **Additional support notes** | If a dealer requests us to update the provider name in the ADF notification, we cannot adjust this In the ADF Notification of forms: Under the Provider Section: **<name><!\\[CDATA\\[Dealer Inspire - Website\\]\\]></name>** this is a Honda Requirement, we cannot change this the Honda Integration plugin will overwrite it! **SOLUTION:** The only place we can add the form title is within the Custom Comments section of the ADF. Please reach out to Content to make _any_ adjustments and with any questions! **CRM Change:** Dealer CRM support starts with iN support at 800-245-4343 (Option #1) Or create HELMS support case to update CRM (instructions below) |

**Honda has transitioned to a new lead management system as of January 2022**

Honda Support options if dealer is having issues with their leads:

- Dealer support starts with iN support at 800-245-4343 (Option #1)
- Cases can also be created and tracked within HELMs. Dealers can ask DSMs (District Sales Managers) to help escalate as DSMs now have visibility to all cases
- Responses times:
  - During first month of HELMS rollout:
    - Severity 1: 4 hours \\[Ex: Leads not getting delivered\\]
    - Severity 2: 8 hours \\[Ex: Dealers not able to view leads\\]
    - Severity 3: 48 hours \\[Ex: Reporting/Cosmetic issues\\]

- Please see FAQs below for more details

## **HELMS Support Guide:**
