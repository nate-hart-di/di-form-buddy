‌

| **Question**                                                                                                                                                                                       | **Answer**                                                                                                                                                                                                                              |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Where do these forms map through? **                                                                                                                                                             | Shift Digital Shift Digital does NOT pass test leads submitted from development sites through to the dealer. Vehicle sale forms are required to map to Shift only. All other forms map directly to Dealer only. DI → Shift → Dealer CRM |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | No, credit apps are routed directly to the dealer and not the OEM Credit App Routing: DI → Dealer                                                                                                                                       |
| **What are the form testing requirements?**                                                                                                                                                        | First Name: DealerInspire Last Name: \\[dealership_name\\]1 Email: \\[dealership_name\\]1@[dealerinspire.com](http://dealerinspire.com)                                                                                                 |
| **Does this brand have any custom forms besides the core 1-16? b**                                                                                                                                 | No                                                                                                                                                                                                                                      |
| **What is the mapping ID and mapping type for each form?** _For example, are the forms mapped as shift and dealer? Dealer only?_                                                                   | See below                                                                                                                                                                                                                               |

| **Does this brand allow custom used lead routing? Please elaborate on if yes or no. Is this up to date?** [**OEM ROUTING APPROVAL**](https://docs.google.com/spreadsheets/d/1BZAqkFxH9KZCAP4wbXftBWUiaN-Swf1lb2ozno3fOfI/edit#gid=0) _Some dealers share inventory for multiple sites and they want routing to be updated based on inventory feeds._  
\_For example, if a MINI site also has their BMW and CDJR inventory feeds, can we update routing on the non-oem feeds to route to their specific OEM’s and bypass the OEM? Or do ALL leads need to route to the OEM, regardless of brand or fe
<truncated 1548 bytes>
turned off.

**SUPPORT:** Please follow directions here for Support cases for updating a Jaguar site to Service or CPO: <custom data-type="smartlink" data-id="id-0">https://carscommerce.atlassian.net/wiki/spaces/JLR/pages/4418077740</custom> |

![](blob:https://media.staging.atl-paas.net/?type=file&localId=null&id=95e1d5d1-8c7c-47bf-aa4a-4c306594a22b&&collection=contentId-448103458&height=626&occurrenceKey=null&width=684&__contextId=null&__displayType=null&__external=false&__fileMimeType=null&__fileName=null&__fileSize=null&__mediaTraceId=null&url=null)
‌

#### FORM MAPPING

- Shift Digital does NOT pass test leads submitted from development sites through to the dealer.
- Vehicle sale forms required to map to Shift only.
- All other forms map directly to Dealer only.

#### TESTING REQUIREMENTS

- First Name: DealerInspire
- Last Name: \\[dealership_name\\]1
- Email: \\[dealership_name\\]1@[dealerinspire.com](http://dealerinspire.com)

#### OEM SPECIFIC FORM FUNCTIONALITY

- Trade In forms
  - Ensure there is a vehicle in the ADF/XML
  - If 'vehicle year' is not set in the ADF/XML, set it to the current model year
  - If 'vehicle make' is not set in the ADF/XML, set it to 'Jaguar'
  - If 'vehicle model' is not set in the ADF/XML, set it to 'Full Line'

- Vehicle Finder Forms (#8)
  - The year dropdown is populated with the current model year and the last 7 years.
  - The 'current model year' increments on November 1 of the current year.
  - For instance, from January 1-October 31 of 2019, the max year (current model year) will be '2019'.
  - On November 1-December 31, the max year will be '2020'.
  - Starting in the calendar year 2020 until October 31, 2020, the max year will remain 2020.

‌

If a dealer changes the CRM address, please update the "dealer only" leads in the back end of the DI site, and email [info@jlrdigital.com](mailto:info@jlrdigital.com) providing them the updated CRM address, if the dealer has not already. 
