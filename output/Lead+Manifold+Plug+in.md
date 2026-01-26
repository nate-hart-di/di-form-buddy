| **What is Lead Manifold?** |
| -------------------------- |

| This is a plugin that allows a user to easily map the form fields to the corresponding sections of the required Lead Submission Data which will be sent on via API instead of ADF/XML for form leads.  
This is to be used for any adjustments made to the core forms 1-16 OR for any custom forms created.  
**NOTE: Lead Manifold must be activated BEFORE a custom form is added to the site.**  
If it is not activated, the form GUID will not be recognized and you will not be able to route the form. |
| **What brands currently use Lead Manifold?** |
| [GMUS](https://dealerinspire.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Manifold+Plugin#Lead-Manifold-for-GM) | [Ford](https://dealerinspire.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Manifold+Plugin#Lead-Manifold-for-Ford) | [Nissan](https://dealerinspire.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Manifold+Plugin#Lead-Manifold-for-Nissan) (Dealers can no longer opt in or out)  
Old opt out list [here](https://docs.google.com/spreadsheets/d/1xBCpmI7RCR570qk0GxdHcKTZikQC_OrPrVpdU1elb5g/edit#gid=0) |
| [Volvo US](https://carscommerce.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Manifold+Plugin#Lead-Manifold-for-Volvo-US) | [Volvo CA](https://carscommerce.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Manifold+Plugin#Lead-Manifold-for-Volvo-US) | [Subaru](https://carsenterprise.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Manifold+Plugin#Lead-Manifold-for-Subaru) |
| [Volkswagen US](https://carscommerce.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Ma
<truncated 54401 bytes>
://share.zight.com/L1uymgGA)  
National Opt-in: [Image 2024-04-03 at 11.56.49 AM](https://share.zight.com/GGudYNZL)

- **Shift Digital Plugin:** Shift Digital is not used for forms and only for tagging. This plugin should remain on for these purposes but should not be used for anything related to forms.  
  The ELMS toggle is on but posting to Shift is blocked programmatically. Any lead duplication would come from the Unite Digital or Gravity Forms ADF/XML side of things, do not loop in shift for any form issues.
- **Form notes:**  
  \- When creating new fields to map do not map/use the **Country** field, or **Vehicles Options** this will break the format.  
  \- For the **Contact Us** Campaign type - No Vehicle Nodes will be added, any VOI fields should be mapped to customer comments instead.  
  If you don't you'll need the status, year, make, and model nodes for the lead to successfully send a lead.

  -([Screenshot](https://share.zight.com/5zuvqKnk)) Name may show up twice, it does not get sent twice. It's a UI visual bug that doesn't impact End User Experience

  -Check to see if you have a successful lead generation on the entry (if you don't, it's either due to a mapping error or escalate accordingly through proper escalation paths) after we get that we can loop in UD support if necessary.

---

## Lead Manifold for MBUS

### See full form notes for this OEM: [HERE](https://carscommerce.atlassian.net/wiki/spaces/MBUS/pages/794959475)

MBUS has DI leads route to MBUS’s API, MBUS will then distribute the leads to the dealers CRM. 

Some dealers have **NOT** signed the T3 leads agreement with MBUS and they will **NOT** be integrated into this lead API update. These dealers will continue to have all leads route directly into the dealers CRM. See list below:

List of Opted Out Dealers: <custom data-type="smartlink" data-id="id-29">https://docs.google.com/spreadsheets/d/1Ijp7jwVtb-14Cfrq5I21xLSpSq2bFFGNQDxb96Fu7BI/edit?usp=sharing</custom>

pending more info
