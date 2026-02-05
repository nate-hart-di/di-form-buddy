| **Question**                                                                                                                                                                                   | **Answer**                                                                                                                                                                                                                                                              |
| ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Where do these forms map through? _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                           | In the dealer is enrolled in the Nissan leads program, Nissan is requiring ELMS lead routing administered through Shift. Nissan has given dealers the option to opt-out of routing their leads through Shift. If they opt out, leads can be sent directly to their CRM. |
| Does this brand route credit apps through the OEM? _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_ | No – we should not be routing credit apps through Nissan ELMS.                                                                                                                                                                                                          |

| What are the form testing requirements? _What are their specific naming conventions or testing rules when we are testing leads_  
_or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_ | No specific naming conventions or testing rules for testing. However, using “test” with customer contact information, or [test@test.com](mailto:test@test.com) as the email address will ensure the test leads are not delivered to dealers. |
| Does this brand have any custom forms besides the core 1-16? if yes, please provide the form title, provider ID, and mapping. _All sites go through development with the core #1-16 forms (Contact Us, Get E-Price, etc), does this brand have any specific forms that are required past these core 16? If so, what are they and how are they used on the site?_ | These are the current DI lead sources for Nissan: Source ID          1517     
<truncated 6411 bytes>
#gid=566577538</custom>

[https://docs.google.com/spreadsheets/d/1xBCpmI7RCR570qk0GxdHcKTZikQC_OrPrVpdU1elb5g/edit#gid=0](https://docs.google.com/spreadsheets/d/1xBCpmI7RCR570qk0GxdHcKTZikQC_OrPrVpdU1elb5g/edit#gid=0)‌

**ELMS Lead Routing Details:**

For Shift-only leads, Nissan is only allowing Shift to send leads to one email address for the primary CRM.

- Any sales lead and custom form that contains a VOI (vehicle of interest) will be routed to Shift first, then dealer.
  - Example forms: Get E-Price, Test Drive, Check Availability, Vehicle Finder, Lock This Payment, etc.
  - VOI includes all New/Used/CPO vehicles

- Any lead that does not contain a VOI will be sent directly to the Dealer from DI. Shift does not want these leads
  - Example forms: Order Parts, Contact Parts, Contact Service, Employment, Schedule Service, etc.

- Lead mapping can be found here: <custom data-type="smartlink" data-id="id-3">https://docs.google.com/spreadsheets/d/1UHC2EUgSuqr_otv61E7r0Ci9wkQeQPeOD6b54SxR9fk/edit#gid=1188355948</custom>

‌

**Implementation:**

- Currently, only Nissan program Sitebuilder sites will get updated to Lead Manifold to send to Shift.
- New program sites will have this update made during production of their new site.
- For program sites that have already launched:
  - The Content team has made the Lead Manifold update to about 90 post go-live sites.
  - They have sent test leads under ‘Contact Parts’ to all affected sites.
  - **PFMs** - Please use this google doc to help verify that test leads have successfully arrived to the dealer’s CRM: <custom data-type="smartlink" data-id="id-4">https://docs.google.com/spreadsheets/d/1JdJLmzOgp8-2HkT_JXbF_XjJr9qcjV5LC_t0qsOmwXM/edit#gid=0</custom>
    - Please mark column “L” with Y or N

- Nissan update to Lead Manifold process: <custom data-type="smartlink" data-id="id-5">https://carscommerce.atlassian.net/wiki/spaces/CD/pages/2671084591</custom>
