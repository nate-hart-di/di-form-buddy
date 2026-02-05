# Lead Form Delivery

## Table of Contents

- [Lead Delivery Tasks](#lead-delivery-tasks)
- [What are Lead Forms](#what-are-lead-forms)
- [Common Terms](#common-terms)
- [OEM and Lead Form Testing](#oem-and-lead-form-testing)
- [OEM Caveats](#oem-caveats)
- [Dashboards and Reports](#dashboards-and-reports)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Setting up Lead Forms](#setting-up-lead-forms)

## Lead Delivery Tasks

- Prelaunch Set up, Test & Confirm Lead Forms
- PGL: Test and Confirm Leads with Client
- PGL: Confirm Lead Manifold (Ford Only)
- PGL: Test & Confirm Shift Test Leads (JLR Only)

## What are Lead Forms

These are the forms that users on the site fill out requesting information from the dealership. The core forms on DI sites are the Gravity Forms below:

1. Get E-Price
2. Order Parts
3. Contact Us
4. Contact Commercial
5. Contact Parts
6. Contact Service
7. Schedule Test Drive
8. Vehicle Finder Service
9. Schedule Service
10. Check Availability
11. Employment
12. Schedule A Bodyshop Appt
13. Value Trade In
14. Lock Lease
15. Lock Payment
16. Ask a Question

**Finance App**: Separate lead form not included in Gravity Forms

## Common Terms

### ADF vs Text Notifications

**ADF**: Auto-Lead Data Format, or ADF, is the format used to send emails to the CRM (Customer Relationship Management). This is what we refer to as the CRM address.

**Text Notification**: General email inbox; gmail, Yahoo, dealership, etc.

### ELMS

**Enterprise Lead Management System (ELMs)** performs a very basic check to ensure the lead meets minimal acceptance criteria. Once the system has completed these steps, it will return a response indicating successful acceptance or failure. A valid lead will be assigned a Lead ID and will then be passed to the dealership CRM. Examples of ELMs that our clients use are Shift, Autodata, and Unite.

### Lead Parsing

Lead parsing is used when Sibling sites within a group wish to have the lead route to the rooftop that possesses the vehicle, even though the form may be sent from another website. In order for this to be present, all sites in the group must share inventory and have destination email addresses coupled with the inventory feed IDʼs.

### Lead Manifold

A platform to accept leads and dispatch to other systems such as Shift, and CRMs. A lead will be accepted with a Success or Failure message. A valid Successful lead will be passed on to the intended target. A Failed lead will need troubleshooting. Common errors are incorrect BAC being used and dealer is not registered with intended target (Shift, OneSource).

## OEM and Lead Form Testing

Each OEM Brand has their own method of assuring that leads get to the client.

Various Providers assist with this: Shift, Autodata, Unite Digital, and FordDirect. When leads are tested, the individual forms are tied to unique Provider ID Numbers within the OEM Plug-in, and this ensures that each lead is captured, processed and when applicable, delivered to the client CRM.

When testing lead forms for these brands, it is important to identify that a Lead Form ID has been generated. This is a ‘pingʼ from the Provider that the lead has entered their system. However, this does not indicate that the lead has been delivered to the client CRM Provider.

Lead form mapping by Brand can be found here.

### OEM Forms Testing by Brand

| Brand                 | OEM Notes Link                                                                                                                       |
| --------------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| Acura                 | [Acura Leads](https://carscommerce.atlassian.net/wiki/spaces/AOP/pages/437387346/Acura+Leads)                                        |
| BMW                   | [BMW Leads](https://carscommerce.atlassian.net/wiki/spaces/AOP/pages/437387346/Acura+Leads)                                          |
| Ford                  | [Lead Forms Routing Ford OEM](https://carscommerce.atlassian.net/wiki/spaces/FOPS/pages/1577943256/Lead+Forms+Routing+Ford+OEM)      |
| Genesis               | [Lead Sources - Genesis](https://carscommerce.atlassian.net/wiki/spaces/Genesis/pages/2947646378/Lead+Sources+-+Genesis)             |
| GM Canada             | [Leads GMCA](https://carscommerce.atlassian.net/wiki/spaces/GMCC/pages/439750308/Leads+GMCA)                                         |
| GM USA                | [GM Leads](https://carscommerce.atlassian.net/wiki/spaces/GMCC/pages/439750308/Leads+GMCA)                                           |
| Honda                 | [HELMS](https://carscommerce.atlassian.net/wiki/spaces/HOP/pages/2820407388/Honda+Enterprise+Lead+Management+System+HELMS)           |
| Hyundai               | [Lead Sources - Hyundai](https://carscommerce.atlassian.net/wiki/spaces/hyundai/pages/2947482382/Lead+Sources+-+Hyundai)             |
| INFINITI              | [INFINITI Leads](https://carscommerce.atlassian.net/wiki/spaces/IOP/pages/439847178/INFINITI+Leads)                                  |
| Jaguar                | [JLR Leads](https://carscommerce.atlassian.net/wiki/spaces/JLR/pages/448103458/JLR+Leads)                                            |
| Kia                   | [KIA Lead Routing](https://carscommerce.atlassian.net/wiki/spaces/KIA/pages/3737551359/KIA+Lead+Routing)                             |
| Land Rover            | [JLR Leads](https://carscommerce.atlassian.net/wiki/spaces/JLR/pages/448103458/JLR+Leads)                                            |
| Lexus                 | [Lexus Leads](https://carscommerce.atlassian.net/wiki/spaces/LEXUS/pages/493327689/Lexus+Leads)                                      |
| Maserati              | [Maserati - Lead Mapping](https://carscommerce.atlassian.net/wiki/spaces/MOP/pages/681313075/Maserati+-+Lead+Mapping)                |
| Mazda CA              | [Leads Mazda CA](https://carscommerce.atlassian.net/wiki/spaces/MZCA/pages/442728468/Leads+Mazda+CA)                                 |
| Mazda USA             | [Mazda Leads Updating CRM](https://carscommerce.atlassian.net/wiki/spaces/MAZ/pages/441025672/Mazda+Leads+Updating+CRM)              |
| Mercedes-Benz CA      | [Leads MBCA](https://carscommerce.atlassian.net/wiki/spaces/MBCA/pages/439747002/Leads+MBCA)                                         |
| Mercedes-Benz USA     | [MBUS Leads](https://carscommerce.atlassian.net/wiki/spaces/MBUS/pages/794959475/MBUS+Leads)                                         |
| MINI                  | [MINI Leads](https://carscommerce.atlassian.net/wiki/spaces/MINI/pages/439749543/MINI+Leads)                                         |
| Mitsubishi            | [Lead Forms Routing Mitsubishi](https://carscommerce.atlassian.net/wiki/spaces/MITSU/pages/452755625/Lead+Forms+Routing++Mitsubishi) |
| Nissan                | [Nissan Lead Routing](https://carscommerce.atlassian.net/wiki/spaces/Nissan/pages/581864214/Nissan+Lead+Routing)                     |
| Porsche               | [Porsche Leads](https://carscommerce.atlassian.net/wiki/spaces/PORSCHE/pages/2735374523/Porsche+Leads)                               |
| Stellantis Alfa Romeo | [Lead Routing - FCA](https://carscommerce.atlassian.net/wiki/spaces/FCA/pages/393183284/Lead+Routing+-+FCA)                          |

## OEM Caveats

### Ford

Lead forms are processed via FordDirect. Any adjustments to ADF/XML Notifications must be managed by the client with their FordDirect Rep.

### GM

Leads are managed via OneSource. Any adjustments to ADF/XML Notifications must be managed by the client with their DDPM or updates can be made within Global Connect.

### Lead Manifold

When testing these brands that are using LM, we must verify that LM is processing the lead correctly and Success is shown on the individual lead(s) tested.

### Other OEM Links

- [Stellantis (FCA) CDJRF](https://carscommerce.atlassian.net/wiki/spaces/FCA/pages/393183284/Lead+Routing+-+FCA)
- [Subaru](https://carscommerce.atlassian.net/wiki/spaces/SUBARU/pages/2848555058/Lead+Forms+Routing++Subaru)
- [Toyota](https://carscommerce.atlassian.net/wiki/spaces/TOYOTA/pages/493393234/Leads+Toyota)
- [Volkswagen CA](https://carscommerce.atlassian.net/wiki/spaces/VWCA/pages/438960675/Leads+VWCA)
- [Volkswagen USA](https://carscommerce.atlassian.net/wiki/spaces/VWUS/pages/439061810/Volkswagen+Leads)
- [Volvo CA](https://carscommerce.atlassian.net/wiki/spaces/VOLVOCA/pages/3414131168/Lead+Forms+Volvo+Canada)
- [Volvo US](https://carscommerce.atlassian.net/wiki/spaces/VUOP/pages/3662970881/Lead+Routing+Volvo+US)

## Dashboards and Reports

We utilize one central Salesforce dashboard for tasks. [Dashboard Link](https://carscommerce.lightning.force.com/lightning/r/Dashboard/01Z5b000000y1IuEAI/view).

From this dashboard, we can see the following reports:

- View Report (Classic Site's Set Up/Test Lead Forms)
- View Report (Prelaunch Forms To Test)
- View Report (Prelaunch Forms to Confirm)
- View Report (PGL Forms To Test)
- View Report (PGL Forms to Confirm)

It is from these reports that we determine the sites that are ready for testing in both the Prelaunch and PGL environments.

## Testing

1. Identify the Service Task for the Project and assign in your name.
2. Open the ST, WR, and Main Account.
3. Identify the main POC.
   - Should be listed in the WR, but you can also verify by looking at Activity and Identifying the individual present on emails from the PM.
4. Send an Alert email to the main POC letting them you will be testing the lead forms on the website. Email templates can be found here.
   - [Lead Manifold Plugin](https://carscommerce.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Manifold+Plug+in)
5. Pull up the testing template for the brand you are testing and make a copy name this new document the same name as the SF Account.
   - Each Brand has a folder which containing the testing templates: [Drive Folder](https://drive.google.com/drive/folders/1EYZktIsoNfDrItGwWxwmd93VQEYumPSq)
6. Open the site and examine leads present on the front end.
   - Not all forms will be present on the site so you can delete any from the testing sheet that will not be in use.
7. Log in to the back end of the website and review what is present.
   - **Shift plugin**: Does the brand require it, and if so, is it activated and does it have the correct dealer code?
   - **Lead Manifold**: Does the brand require it and are all forms mapped? Additionally, are ADFs appropriately disabled?
8. Open Forms from the left side menu and then open Notifications for your first form.
   - It will show ADF Notifications and Text Notifications.
   - Open each and copy the email that is present and paste it into your testing sheet.
   - Repeat this for all active forms on the website.
9. You may now begin testing each form using the names present on the testing sheet.
   - You must use different names and email addresses on each form. The client CRM will list all test leads as one submission if the same name and email is used more than once.
10. Depending on the plugin being used, you will need to verify if it has processed correctly.
    - **Shift**: determine if a Shift Lead ID number is present on the submission.
    - **Lead Manifold**: Determine that a Success message was generated after the lead was submitted.
    - If either of these fail to generate, see Troubleshooting section.
11. Once all forms have been tested you will need to send a confirmation email to the POC. Email templates are found here:
    - [Template Link](https://docs.google.com/document/d/1xBNvrEiUG9_LVzzuop82xKnKAC2hvmJyfxgrHmt6zE/edit?pli=1#)
12. Move Service Task cycle to appropriate next step after email is sent.
    - **Prelaunch**: move to Cycle 3.
    - **PGL**: move to Cycle 2.
    - This will move the tasks to the respective Confirmation report.
13. **Confirmations**
    - Once the client confirms receipt of all leads for either environment, mark the Service Task Complete.

## Troubleshooting

See [Troubleshooting Forms](https://carscommerce.atlassian.net/wiki/spaces/CD/pages/531890867/Troubleshooting+Forms).

## Setting up Lead Forms

1. Once website project is in the Stage ‘In Development/Contentʼ send email to POC to confirm CRM and Text Notification addresses for leads.
2. Follow Production Development Confluence space here.
3. Once forms are set up, you can set up to test the leads on the dev link.
