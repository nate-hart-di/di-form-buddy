‌

| **Question**                                                                                                                                                                                       | **Answer**                              |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------- |
| **Where do these forms map through? ** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                          | Shift                                   |
| All sales leads should be routed through Shift. All service/parts leads route straight to dealer but can be received by Shift and will be treated as a “safety copy” and not delivered to dealer.  |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | No these should route direct to dealer. |

| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_ | None, if there are no sale leads, Dealer Inspire does not need to route these leads through Shift Digital. They can be routed directly to the dealership. |
| **Does this brand have any custom forms besides the core 1-16? if yes, please provide the form title, provider ID, and mapping.** _All sites go through development with the core #1-16 forms (Contact Us, Get E-Price, etc), does this brand have any specific forms that are required past these core 16? If so, what are they and how are they used on the site?_ | No. Only custom forms created for brand specific pages pushed out programatically. |
| **What is the mapping ID and mapping type for each form?** _For example, are the forms mapped as shift and dealer? Dealer only?_ | See below |
| **D
<truncated 1257 bytes>
e activated based on the buy/sell sale?\_  
*Anything else form specific to know about Buy/Sells?* | buy/sells we switch over the dealer code and lead routing would also switch to the new dealer code. |
| **Additional Support Notes\*\* | For Lead support, reach out to the Shift PDWS: <custom data-type="smartlink" data-id="id-0">https://docs.google.com/spreadsheets/d/1TvNwiBEnNqmd0Au7AgfinDxUp-u6quyjiqaoLNKJN4E/edit?gid=1912113194#gid=1912113194</custom> |

| **DI Form Name** | **Shift ID** | **Mapping (Shift Only, Dealer Only, Both)** |  
 |
| --- | --- | --- | --- |
| 1' // Get E-Price | 465 | Shift Only | \*All vehicles: New, Used, CPO |
| 2' // Order Parts | 466 | Both |  
 |
| 3' // Contact Us | 467 | Both |  
 |
| 4' // Contact Commercial | 468 | Both |  
 |
| 5' // Contact Parts | 469 | Both |  
 |
| 6' // Contact Service | 470 | Both |  
 |
| 7' // Schedule Test Drive | 471 | Shift Only | \*All vehicles: New, Used, CPO |
| 8' // Vehicle Finder | 473 | Shift Only | \*All vehicles: New, Used, CPO |
| 9' // Schedule Service | 472 | Both |  
 |
| 10' // Check Availability | 474 | Shift Only | \*All vehicles: New, Used, CPO |
| 11' // Employment | 475 | Both |  
 |
| 12' // Schedule Bodyshop Appointment | 476 | Both |  
 |
| 13' // Value Your Trade | 478 | Shift Only | \*All vehicles: New, Used, CPO |
| 14' // Lock this Lease | 479 | Shift Only | \*All vehicles: New, Used, CPO |
| 15 // Lock this Payment | 480 | Shift Only | \*All vehicles: New, Used, CPO |
| 16' // Ask a Question | 481 | Both |  
 |

‌

~~Leads | Porsche | 2021 October~~

**~~Standalone Service Center website: ~~**

- ~~If there are no sale leads, Dealer Inspire does not need to route these leads through Shift Digital. They can be routed directly to the dealership.~~

**~~Regular Porsche Website:~~**

- ~~All Inventory forms go through Shift only, all others go to both (Shift will archive).~~
- ~~Sales leads won’t route to the ELMS/FLOW system until the site is live.~~
