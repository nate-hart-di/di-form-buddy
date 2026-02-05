| **Question**                                                                                                                                                                                       | **Answer**                 |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------- |
| **Where do these forms map through?** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                           | Shift                      |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | Yes - routes through Shift |

| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_ | **Lead Form Testing Requirements:** First Name: DealerInspire  
Last Name: \\[dealership_name\\]1  
Email: \\[dealership_name\\]1@[dealerinspire.com](http://dealerinspire.com/) **Note**: Shift does not pass images through to the dealer (Value Trade form) ![](blob:https://media.staging.atl-paas.net/?type=file&localId=null&id=7667c29f-fb4a-4b90-8c09-bc6ef8247001&&collection=contentId-393183284&height=366&occurrenceKey=null&width=936&__contextId=null&__displayType=null&__external=false&__fileMimeType=null&__fileName=null&__fileSize=null&__mediaTraceId=null&url=null)  
 |
| **Does this brand have any custom forms besides the core 1-16?** if yes, please provide the form title, provider ID, and mapping. _All sites go through development with the core #1-16 forms (Contact Us, Get E-Price, etc), does this brand have any specific forms that are required past these core 16? If so, what are they and how are they used on the site?_ | No |
| \*\*What is the mapping ID and mapping ty
<truncated 2989 bytes>
tact Commercial (4) | 531 | Shift Only |
| Contact Parts (5) | 539 | Shift and Dealer |
| Contact Service (6) | 539 | Shift and Dealer |
| Schedule Test Drive (7) | 528 | Shift Only |
| Vehicle Finder Service (8) | 535 | Shift and Dealer |
| Schedule Service (9) | 539 | Shift and Dealer |
| Check Availability (10) | 538 | Shift Only |
| Employment (11) | N/A | Dealer Only |
| Schedule a Bodyshop Appointment (12) | 539 | Shift and Dealer |
| Value Trade In (13) | 527 | Shift and Dealer |
| Lock This Lease (14) | 534 | Shift Only |
| Lock This Payment (15) | 534 | Shift Only |
| Ask a Question (16) | 530 | Shift and Dealer |
| Dodge Power Broker | 1834 | Shift and Dealer |

**DI CREDIT APPLICATION**

**How DI Credit App is Routed:** DI > Shift (Formerly Autodata/JDP) > Dealer

|  
 | **Shift (formerly Autodata / JDP) Affiliate ID** | Send To |
| --- | --- | --- |
| DI Credit App | 536 | Shift Only |

**ONLINE SHOPPER**

**How Online Shopper is Routed:** DI > Shift (Formerly Autodata/JDP) > Dealer

| DRS Tool               | **Shift (formerly Autodata / JDP) Affiliate ID** | Send To    |
| ---------------------- | ------------------------------------------------ | ---------- |
| Electric               | 1231                                             | Shift Only |
| Off Platform (Redline) | 1230                                             | Shift Only |

**CONVERSATIONS**

How Conversations is Routed: DI > Shift (Formerly Autodata/JDP) > Dealer

| Conversations Lead                         | **Shift (formerly Autodata / JDP) Affiliate ID** | Send To          |
| ------------------------------------------ | ------------------------------------------------ | ---------------- |
| CHAT - Sales Lead w/ Valid VOI             | 566                                              | Shift Only       |
| SMS - Sales Lead w/ Valid VOI              | 1804                                             | Shift Only       |
| CHAT & SMS - Sales Lead no VOI (Full Line) | 604                                              | Shift and Dealer |
| Service Lead                               | N/A                                              | Dealer Only      |

\**Note: non-VOI leads are not currently passed through to dealer CRMs, they are stored within Autodata's LMS for reporting only.* 

**ACCU-TRADE **

**How Accu-Trade is Routed:** DI > Shift (Formerly Autodata/JDP) > Dealer

|  
 | Autodata/JDP Affiliate ID | Send To |
| --- | --- | --- |
| Dealer Inspire - Accu-Trade | 1870 | Shift Only |
