In order to send leads through Urban Science, the dealer code must be 5 digits.

Example: 12340

| **Question**                                                                                                                                                                                       | **Answer**                                                                                                              |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| **Where do these forms map through?** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                           | Sales leads are sent ADF through Urban Science Parts & Service leads are sent to whatever CRM the dealer has requested. |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | No                                                                                                                      |

| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_ | Urban Science does not require we confirm test leads in dev or prod. Please perform any necessary testing directly with the dealer |
| **Does this brand have any custom forms besides the core 1-16?** if yes, please provide the form title, provider ID, and mapping. _All sites go through development with the core #1-16 forms (Contact Us, Get E-Price, etc), does this brand have any specific forms that are required past these core 16? If so, what are they and how are they used on the site?_ | No |
| **What is the mapping ID and mapping type for each form?** _For example, are the forms mapped as shift and dealer? Dealer only?_ | See breakdown below. |
| **Does this brand allow custom used lead routing? Please elaborate on if yes or no. Is this up to date?** [**OEM ROUTING
<truncated 813 bytes>
oes your brand have any similar requirements or rules we need to be aware of?\_ | Any SALES LEAD CRM changes will need to go to Volvo. Please loop in the dealer’s regional Digital Strategy Specialist. |
| **Buy/Sell and forms\*\* _How are forms tested or updated when a site goes through a buy/sell? Are live site updates allowed?_  
_When is a new dealer code activated based on the buy/sell sale?_  
_Anything else form specific to know about Buy/Sells?_ | Test with dealer as we would for a new build. |

Program Documentation: <custom data-type="smartlink" data-id="id-0">https://drive.google.com/drive/folders/1MHNAd8QdPzpNbXz0m9dPcoXimK251k6a</custom>

‌

Leads are sent to dealers via ADF.

Lead routing is managed via Lead Manifold (LM)

All SALES leads (see below) flow through Urban Science. Urban Science works with Volvo to send the lead to the dealer’s preferred CRM.

| **Form Title**                                  | **Lead Description**                        |
| ----------------------------------------------- | ------------------------------------------- |
| Get E-Price                                     | Urban Science                               |
| Contact Us                                      | General Administration Contact Form Request |
| Contact Commercial                              | Urban Science                               |
| Schedule Test Drive                             | Urban Science                               |
| Vehicle Finder Service                          | Urban Science                               |
| Check Availability                              | Urban Science                               |
| Value Your Trade                                | Urban Science                               |
| Lock this Lease                                 | Urban Science                               |
| Lock this Payment                               | Urban Science                               |
| Ask a Question                                  | Urban Science                               |
| Vehicle Configurator - Lead form                | Urban Science                               |
| Vehicle Configurator- Schedule Appointment form | Urban Science                               |
| Online Shopper                                  | Urban Science                               |
| Conversations                                   | Urban Science                               |
| Contact Parts                                   | Direct to Dealer                            |
| Contact Service                                 | Direct to Dealer                            |
| Order Parts                                     | Direct to Dealer                            |
| Schedule a Body Shop Appointment                | Direct to Dealer                            |
| Schedule Service                                | N/A - Dealers should ONLY use xTime         |

- Any SALES LEAD CRM changes will need to go to Volvo. Please loop in the dealer’s regional Digital Strategy Specialist.

SERVICE and PARTS leads send directly to the dealer.

Dealers can set up text notifications if requested.

‌
