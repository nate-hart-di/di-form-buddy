| **Question**                                                                                                                                                                                                                 | **Answer**                                                                                                                                                                                                                                                   |
| ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Where do these forms map through?                                                                                                                                                                                            | Forms will route either LM to Volvo CA via JSON lead delivery Direct to dealer. In this instance, dealer should be using Rapid Response (Keyloop)                                                                                                            |
| Does this brand route credit apps through the OEM?                                                                                                                                                                           | Direct to dealer                                                                                                                                                                                                                                             |
| What are the form testing requirements?                                                                                                                                                                                      | JSON can be confirmed on backend of site Rapid Response can be tested direct with dealer. When testing either JSON or Rapid Response, please include JUNK THIS in comments section. If no comments section exists on form, include JUNK THIS in name fields. |
| Does this brand have any custom forms besides the core 1-16? if yes, please provide the form title, provider ID, and mapping.                                                                                                | No Any dealer requesting custom forms must re-use a pre-existing form with one of the pre-defined campaign codes.                                                                                                                                            |
| What is the mapping ID and mapping type for each form?                                                                                                                                                                       | Please see full list below                                                                                                                                                                                                                                   |
| Does this brand allow custom used lead routing? Please elaborate on if yes or no. Is this up to date? [OEM ROUTING APPROVAL](https://docs.google.com/spreadsheets/d/1BZAqkFxH9KZCAP4wbXftBWUiaN-Swf1lb2ozno3fOfI/edit#gid=0) | No                                                                                                                                                                                                                                                           |
| Does this brand have any custom form requirements/notes?                                                                                                                                                                     | Yes, postal code must be present on all forms. Forms on VDPs should include a VOI                                                                                                                                                                            |
| Buy/Sell and forms                                                                                                                                                                                                           | Same testing as site go-live.                                                                                                                                                                                                                                |

Rapid Response (Keyloop) is the CRM Provider for all Volvo CA sites; every dealer has a RR CRM address. Some of the form leads will go directly to the dealer while others will first go to Volvo and then forwa
<truncated 384 bytes>
4 | Contact Commercial | Dealer Only | Campaign: DI_T3_contact_request Contact Request |
| 5 | Contact Parts | Dealer Only | Campaign: DI_T3_contact_request Contact Request |
| 6 | Contact Service | Dealer Only | Campaign: DI_T3_contact_request Contact Request |
| 7 | Schedule Test Drive | OEM Only | Campaign: DI_T3_test_drive_request Test Drive Request |
| 8 | Vehicle Finder Service | OEM Only | Name: Contact request Set to RAPID RESPONSE ONLY routing |
| 9 | Schedule Service | N/A - Dealers ONLY use xTime | Campaign: DI_T3_contact_request Contact Request |
| 10 | Check Availability | OEM Only | Campaign: DI_T3_contact_request Contact Request |
| 11 | Employment | Dealer Only | Campaign: DI_T3_contact_request Contact Request |
| 12 | Schedule Bodyshop Appt | Dealer Only | Campaign: DI_T3_contact_request Contact Request |
| 13 | Value Trade In | OEM Only | Campaign: DI_T3_trade_in_request Financial Service Request |
| 14 | Lock This Lease | OEM Only | Campaign: DI_T3_trade_in_request Financial Service Request |
| 15 | Lock This Payment | OEM Only | Campaign: DI_T3_trade_in_request Financial Service Request |
| 16 | Ask a Question | OEM Only | Campaign: DI_T3_contact_request Contact Request |

‌

### LEAD MANIFOLD

- “OEM Only” are sent to the brand in JSON format.
- “Dealer Only” routed forms can be confirmed in SendGrid.
- VERSION: Lead Manifold Version 1.17.0 or higher is required on Volvo CA sites.

### CUSTOM FORMS

Any custom form must be from a re-purposed form using one of the pre-defined campaign codes.

### BRAND REQUIREMENTS

Preferred Language - is a Volvo CA requirement, and LM will not process the lead if this is hidden on the forms

- Concern here is that if it is hidden again in the future, the leads will not function.

Postal code must be present on all forms.

### INTERNAL ESCALATION PROCESS

Issues with leads being received by the dealer and/or Rapid Response can be escalated to the Lead Delivery Team (Bob Hipps, Supervisor).

‌

‌
