| **Question**                                                                                                                                                                                       | **Answer**                                                                                                                                                                                                                                                                                                                                                                                                                    |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Where do these forms map through? ** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                          | Shift Leads for new and used vehicles cannot bypass Mazda. Shift ELMS powers Mazda analytics and data tracking for MNAO “Any New, Used, or Certified Vehicle sales leads will be sent **only** to Shift Digital as outlined in the following document. The website provider will be responsible for delivering any non-sale specific leads (service, parts, general contact, etc.) to both Shift Digital **and** the dealer.” |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | Not on program                                                                                                                                                                                                                                                                                                                                                                                                                |

| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_ | Before testing can begin, the lead provider must be issued source IDs by Shift Digital’s technical team.  Any credentials or URLs referenced in this document are for the staging instance of ELMS.  Production credentials and URLs will be shared upon successful completion of integration testing. |
| **Does this brand have any custom forms besides the core 1-16? if yes, please provide the form title, provider ID, and mapping.** \_All sites go through development with the core #1-16 forms (Contact Us, Get E-Price, etc), does this brand have any specific forms th
<truncated 3013 bytes>
request comes through to MDCP, they are able to pass through and confirm a test lead with the dealer to ensure everything is operating as it should. If the dealer would like leads routed to a secondary address, DI Support would need to enable a second route in the back end of the site and/or update accordingly to be able to post to a specific lead address. |

Mazda Lead Document :

Please see here for [**Mazda - Shift Lead Source ID's **](https://docs.google.com/spreadsheets/d/1L4q093-_-n6Fb1UeCsNJF-v5P7V9icSu/edit#gid=482244710)

- Mapping settings should not be changed per program requirements. 
- Leads for new and used vehicles cannot bypass Mazda. Shift ELMS powers Mazda analytics and data tracking for MNAO. 
- Shift does not post leads via ADF XML address. Rather, they direct post leads to the dealers CRM tool.
- Any Used vehicle leads from a Mazda Program site sent through the Mazda program will be delivered to the Mazda CRM. Shift does not have the capability to send leads to another OEM CRM based on where the inventory lives.

The following are source IDs that Shift archives for Dealer Inspire. Meaning Shift are receiving them to Shift ELMS, but do not deliver to dealer.  So Dealer Inspire should be delivering them instead.

| SourceID | Name                                             | SourceProperty | Value |
| -------- | ------------------------------------------------ | -------------- | ----- |
| 1357     | Dealer Inspire - Ask a Question                  | LeadType       | 6     |
| 1346     | Dealer Inspire - Contact Parts                   | LeadType       | 5     |
| 1347     | Dealer Inspire - Contact Service                 | LeadType       | 4     |
| 1344     | Dealer Inspire - Contact Us                      | LeadType       | 6     |
| 1352     | Dealer Inspire - Employment                      | LeadType       | 6     |
| 1343     | Dealer Inspire - Parts                           | LeadType       | 5     |
| 1353     | Dealer Inspire - Schedule Bodyshop Appointment   | LeadType       | 4     |
| 1350     | Dealer Inspire - Schedule Service                | LeadType       | 4     |
| 1469     | Dealer Inspire - Order Parts                     | LeadType       | 5     |
| 1471     | Dealer Inspire - Schedule a Bodyshop Appointment | LeadType       | 4     |
| 1633     | Dealer Inspire - Conversations - P&S Chat        | LeadType       | 4     |

‌

‌
