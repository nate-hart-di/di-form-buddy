### Lead routing to Unite Digital requires JSON

VW’s lead program transitioned to Unite Digital 3/20/24

**Support Info:**

Unite Program Team: [vwgoaprogramssupport@unitedigital.com](mailto:vwgoaprogramssupport@unitedigital.com)

CRM changes and support: [support@vwdigitalprogram.com](mailto:support@vwdigitalprogram.com) (855) 725-6989 (myVW)

‌

| **Question**                                                                                                                                                                                       | **Answer**                                                                                                                                                                                                                                                                                                                                                                              |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Where do these forms map through?** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                           | Unite Digital - transitioned as of 3/20/24 **Sales Leads = DI → Unite Digital → Dealer CRM via their API (not using CRM addresses)** **After Sales Leads = DI → Dealership CRM directly, DI → Unite Digital (just a copy, not delivered to dealer)** **Credit apps = DI → Dealership CRM directly _\*\*\*this is changing to Unite Digital - in progress with Modern Retailing Team._** |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | Currently No, but Unite has confirmed routing is required through them. We are updating routing via: [https://carscommerce.atlassian.net/jira/servicedesk/projects/SDPOP/queues/issue/SDPOP-2036](https://carscommerce.atlassian.net/jira/servicedesk/projects/SDPOP/queues/issue/SDPOP-2036)                                                                                           |

| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testi
<truncated 2286 bytes>
site also has their BMW and CDJR inventory feeds, can we update routing on the non-oem feeds to route to their specific OEM’s and bypass the OEM? Or do ALL leads need to route to the OEM, regardless of brand or feed?_ | Yes. This can only be for USED vehicles. Dealers cannot share other NEW makes on a VW website (only VWs). Unite does **not** require a copy of leads for shared Used inventory. Only deliver leads to Unite for the VW website/location inventory. If custom routing is requested, route this through Content Support. Example ticket: [02132238](https://carscommerce.atlassian.net/wiki/spaces/paidsearch/pages/2314608517) ![](blob:https://media.staging.atl-paas.net/?type=file&localId=null&id=8eef832c-3826-408d-9514-6326fd91c6b9&&collection=contentId-439061810&height=262&occurrenceKey=null&width=1287&__contextId=null&__displayType=null&__external=false&__fileMimeType=null&__fileName=null&__fileSize=null&__mediaTraceId=null&url=null) |
| **Does this brand have any custom form requirements/notes?** _Please provide clear details. Stellantis for example requires a zipcode field on all forms and this cannot be removed, does your brand have any similar requirements or rules we need to be aware of?_ | First name, last name, email, phone and VOI on Sales forms. |
| **Buy/Sell and forms** _How are forms tested or updated when a site goes through a buy/sell? Are live site updates allowed?_  
_When is a new dealer code activated based on the buy/sell sale?_  
_Anything else form specific to know about Buy/Sells?_ | Dealer code is activated with VW releases the Dealer Change Notice (DCN). Timing on this varies. Website cannot go live without DCN. Leads should be tested in dev, but can be tested live if necessary. Unite receives a daily file from VW that includes dealer code. |
| _**Additional support notes**_ | For Lead Support, e-mail [vwgoaprogramssupport@unitedigital.com](mailto:vwgoaprogramssupport@unitedigital.com) |

\*Do not send Employment Forms through Unite Digital, send as dealer only.

‌
