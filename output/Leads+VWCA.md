### Lead routing to Unite requires JSON

VW’s lead program transitioned to Unite Digital 4/2/24

Unite Support Email: [vwgoaprogramssupport@unitedigital.com](mailto:vwgoaprogramssupport@unitedigital.com)

CRM changes and support: [support@vwdigitalprogram.com](mailto:support@vwdigitalprogram.com) or (844) 717-6989 (myVW)

|                                                                                                                                                                                                    | **Answer**                                                                                                                                                                                                                                                                                                |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Where do these forms map through?** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                           | **- Sales Leads = DI → Unite Digital → Dealer CRM via their API (not using CRM addresses)** **- After Sales Leads = DI → Dealership CRM directly, DI → Unite Digital (just a copy, not delivered to dealer)** **- Credit apps = DI → Dealership CRM directly (this may change with Unite in the future)** |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | No - at this time we are sending Credit Apps straight to the dealership. Unite does want to accept them at some point, which will be a future feature request for Dealer Inspire.                                                                                                                         |

| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_ | TESTING REQUIREMENTS For all leads submitted: Different first and last name No use of the word ‘Test’ (or variations of it) in the contact information No numbers within the name fields Use a valid dealer code (all exi
<truncated 2381 bytes>
rs cannot share other NEW makes on a VW website (only VWs). Unite does **not** require a copy of leads for shared Used inventory. Only deliver leads to Unite for the VW website/location inventory. If custom routing is requested, route this through Content Support. Example ticket: [02132238](https://carscommerce.atlassian.net/wiki/spaces/paidsearch/pages/2314608517) ![](blob:https://media.staging.atl-paas.net/?type=file&localId=null&id=d8f1d14c-79a3-4317-a4a1-4d6570bb149d&&collection=contentId-438960675&height=262&occurrenceKey=null&width=1287&__contextId=null&__displayType=null&__external=false&__fileMimeType=null&__fileName=null&__fileSize=null&__mediaTraceId=null&url=null) |
| **Does this brand have any custom form requirements/notes?** _Please provide clear details. Stellantis for example requires a zipcode field on all forms and this cannot be removed, does your brand have any similar requirements or rules we need to be aware of?_ | Preferred language, dealer opt-in, national opt-in |
| **Buy/Sell and forms** _How are forms tested or updated when a site goes through a buy/sell? Are live site updates allowed?_  
_When is a new dealer code activated based on the buy/sell sale?_  
_Anything else form specific to know about Buy/Sells?_ | Dealer code is activated with VW releases the Dealer Change Notice (DCN). Timing on this varies. Website cannot go live without DCN. Leads should be tested in dev, but can be tested live if necessary. Unite receives a daily file from VW that includes dealer code. |

‌

\*Additional Notes

- LM does not normalize Used vehicle type, only Certified Pre Owned. (Unite not accepting Pre-Owned vehicle type pulled from SRP)
  - IDP toggle can be overridden so Pre-Owned can flip to Used (allowing leads to flow)
  - [ https://share.zight.com/eDueqOD9 ](https://urldefense.com/v3/__https://share.zight.com/eDueqOD9__;!!KxydHg!VIcSjF-Gg043xWSKI2CQoq1wWztfbYeQVBAPEUotlrkb_bRkH2ZZt0ORTzesoLFDwXbykki6f265L08h9sF8gHYartg$)
  - SDWEB-18025
