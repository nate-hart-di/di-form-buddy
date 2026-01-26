‌

| **Question**                                                                                                                                                                                       | **Answer**                                                                                                                                                                                                                                                                                                                                                                                                                     |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Where do these forms map through? ** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                          | Form leads go through Shift Digital.                                                                                                                                                                                                                                                                                                                                                                                           |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | Credit Application functionality forms are powered by various 3rd Party DRS Providers. These lead forms are filled out after the prospect goes through the credit application process on a dealer’s website. In some instances there may be multiple types of Credit Application forms: a) DRS Short credit application forms (pre-qualify); b) DRS Full Credit Application forms; c) Other “Non-DRS “Credit Application Forms |

| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_ | Providers should tag and track all lead and contact request forms with Shift Lead IDs. First Name: DealerInspire Last Name: \\[dealership_name\\]1 Email: \\[dealership_name\\]1@dealerinspire.com |
| **Does this brand have any custom forms besides the core 1-16? if yes, please provide the form title, provider ID, and mapping.** _All sites go through development with the core #1-16 forms (Contact Us, Get E-Price, etc), does this brand have any specific forms that are required past these core 16? If so, what are they and how are they used on the site?_ | No additional forms besides 1-16. |
| **Additional support notes** | **NOTE:** Sometime prior to November 2024, Shift updated mapping of Parts and Service lead forms so they are mapped Shift Only on their end. This means that Shift is no longer archiving these leads and are instead sending these to the client CRM Provider.  
In order for a Genesis website to not have P&S lead forms be delivered to their CRM, the CRM Provider will need to filter these out based on the individual Siebel Code for each form.  
The source id information is within the ADF that is sent over as shown below. |

Lead Sources - Genesis - April 2024

## Siebel Codes and Lead Routing

| CORE FORM NAME                | SHIFT LEAD ID | TYPE/ENDPOINT | DESTINATION |
| ----------------------------- | ------------- | ------------- | ----------- |
| Get E-Price                   | 91            | Leads         | Shift Only  |
| Parts                         | 320           | Parts         | All         |
| Contact Us (General)          | 315           | Contacts      | All         |
| Contact Us (Sales)            | 315           | Contacts      | Shift Only  |
| Contact Us (Service)          | 314           | Service       | All         |
| Contact Us (Parts)            | 313           | Parts         | All         |
| Contact Us (Finance)          | 315           | Contacts      | Shift Only  |
| Contact Commercial            | 315           | Contacts      | All         |
| Contact Parts                 | 313           | Parts         | All         |
| Contact Service               | 314           | Service       | All         |
| Schedule Test Drive           | 97            | Leads         | Shift Only  |
| Vehicle Finder                | 100           | Leads         | Shift Only  |
| Schedule Service              | 322           | Service       | All         |
| Check Availability            | 90            | Leads         | Shift Only  |
| Employment                    | 317           | Contacts      | All         |
| Schedule Bodyshop Appointment | 321           | Service       | All         |
| Value Your Trade              | 98            | Leads         | Shift Only  |
| Lock this Lease               | 318           | Leads         | Shift Only  |
| Lock this Payment             | 319           | Leads         | Shift Only  |
| Ask A Question                | 89            | Leads         | Shift Only  |
| Reservation                   | 96            | Leads         | Shift Only  |
