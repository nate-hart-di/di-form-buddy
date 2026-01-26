# Lead Form Delivery 

 Lead Delivery Tasks What are Lead Forms Common Terms OEM and Lead Form Testing OEM Caveats Dashboards and Reports Testing Troubleshooting Setting up Lead Forms 

## Lead Delivery Tasks 

 Prelaunch Set up, Test & Confirm Lead Forms PGL PGL: Test and Confirm Leads with Client PGL: Confirm Lead Manifold (Ford Only) PGL: Test & Confirm Shift Test Leads (JLR Only) 

## What are Lead Forms 

These are the forms that users on the site fill out requesting information from the dealership. The core forms on DI sites are the Gravity Forms below 

1 Get E-Price 

2 Order Parts 

3 Contact Us 

4 Contact Commercial 

5 Contact Parts 

6 Contact Service 

7 Schedule Test Drive 

8 Vehicle Finder Service 

9 Schedule Service 


10 Check Availability 

11 Employment 

12 Schedule A Bodyshop Appt 

13 Value Trade In 

14 Lock Lease 

15 Lock Payment 

16 Ask a Question 

Finance App Separate lead form not included in Gravity Forms 

## Common Terms 

ADF vs Text Notifications 

ADF = Auto-Lead Data Format, or ADF, is the format used to send emails to the CRM (Customer Relationship Management). This is what we refer to as the CRM address. 

Text Notification = general email inbox; gmail. Yahoo, dealership, etc 

### ELMS 

Enterprise Lead Management System (ELMs) performs a very basic check to ensure the lead meets minimal acceptance criteria. Once the system has completed these steps, it will return a response indicating successful acceptance or failure. A valid lead will be assigned a Lead ID and will then be passed to the dealership CRM. Examples of ELMs that our clients use are Shift, Autodata, and Unite. 

Lead Parsing 

Lead parsing is used when Sibling sites within a group wish to have the lead route to the rooftop that possesses the vehicle, even though the form may be sent from another website. In order 


for this to be present, all sites in the group must share inventory and have destination email addresses coupled with the inventory feed IDʼs. 

Lead Manifold 

A platform to accept leads and dispatch to other systems such as Shift, and CRMs. A lead will be accepted with a Success or Failure message. A valid Successful lead will be passed on to the intended target. A Failed lead will need troubleshooting. Common errors are incorrect BAC being used and dealer is not registered with intended target (Shift, OneSource). 

## OEM and Lead Form Testing 

Each OEM Brand has their own method of assuring that leads get to the client. 

Various Providers assist with this Shift, Autodata, Unite Digital, and FordDirect. When leads are tested, the individual forms are tied to unique Provider ID Numbers within the OEM Plug-in, and this ensures that each lead is captured, processed and when applicable, delivered to the client CRM. 

When testing lead forms for these brands, it is important to identify that a Lead Form ID has been generated. This is a ‘pingʼ from the Provider that the lead has entered their system. However, this does not indicate that the lead has been delivered to the client CRM Provider. 

Lead form mapping by Brand can be found here 

Below re links showing the testing requirements for each current OEM Brand: 

 OEM Forms Testing by Brand 

 Acura 

### BMW 

 Brand OEM Notes https://carscommerce.atlassian.net/ wiki/spaces/AOP/pages/437387346 /Acura+Leads BMW Leads 


Ford 

Genesis 

GM Canada 

### GM USA 

Honda 

Hyundai 

### INFINITI 

Jaguar 

Kia 

Land Rover 

Lexus 

 https://carscommerce.atlassian.net/ wiki/spaces/FOPS/pages/15779432 56/Lead+Forms+Routing+Ford+OE M https://carscommerce.atlassian.net/ wiki/spaces/Genesis/pages/294764 6378/Lead+Sources+-+Genesis https://carscommerce.atlassian.net/ wiki/spaces/GMCC/pages/4397503 08/Leads+GMCA GM Leads https://carscommerce.atlassian.net/ wiki/spaces/HOP/pages/28204073 88/Honda+Enterprise+Lead+Manag ement+System+HELMS https://carscommerce.atlassian.net/ wiki/spaces/hyundai/pages/294748 2382/Lead+Sources+-+Hyundai https://carscommerce.atlassian.net/ wiki/spaces/IOP/pages/439847178/ INFINITI+Leads https://carscommerce.atlassian.net/ wiki/spaces/JLR/pages/448103458/ JLR+Leads https://carscommerce.atlassian.net/ wiki/spaces/KIA/pages/3737551359 /KIA+Lead+Routing https://carscommerce.atlassian.net/ wiki/spaces/JLR/pages/448103458/ JLR+Leads https://carscommerce.atlassian.net/ wiki/spaces/LEXUS/pages/4933276 


Maserati 

Mazda CA 

Mazda USA 

Mercedes-Benz CA 

Mercedes-Benz USA 

### MINI 

Mitsubishi 

Nissan 

Porsche 

Stellantis Alfa Romeo 

 89/Lexus+Leads https://carscommerce.atlassian.net/ wiki/spaces/MOP/pages/681313075 /Maserati+-+Lead+Mapping https://carscommerce.atlassian.net/ wiki/spaces/MAZ/pages/441025672 /Mazda+Leads+Updating+CRM https://carscommerce.atlassian.net/ wiki/spaces/MZCA/pages/4427284 68/Leads+Mazda+CA https://carscommerce.atlassian.net/ wiki/spaces/MBCA/pages/4397470 02/Leads+MBCA 

 https://carscommerce.atlassian.net/ wiki/spaces/MBUS/pages/7949594 75/MBUS+Leads https://carscommerce.atlassian.net/ wiki/spaces/MINI/pages/43974954 3/MINI+Leads https://carscommerce.atlassian.net/ wiki/spaces/MITSU/pages/4527556 25/Lead+Forms+Routing++Mitsubishi https://carscommerce.atlassian.net/ wiki/spaces/Nissan/pages/5818642 14/Nissan+Lead+Routing https://carscommerce.atlassian.net/ wiki/spaces/PORSCHE/pages/2735 374523/Porsche+Leads https://carscommerce.atlassian.net/ wiki/spaces/FCA/pages/393183284 /Lead+Routing+-+FCA 


## OEM Caveats 

Ford 

Lead forms are processed via FordDirect. Any adjustments to ADF/XML Notifications must be managed by the client with their FordDirect Rep 

### GM 

 Stellantis (FCA) CDJRF 

 Subaru 

 Toyota 

 Volkswagen CA 

 Volkswagen USA 

 Volvo CA 

 Volvo US 

 https://carscommerce.atlassian.net/ wiki/spaces/FCA/pages/393183284 /Lead+Routing+-+FCA https://carscommerce.atlassian.net/ wiki/spaces/SUBARU/pages/28485 55058/Lead+Forms+Routing++Subaru https://carscommerce.atlassian.net/ wiki/spaces/TOYOTA/pages/49339 3234/Leads+Toyota https://carscommerce.atlassian.net/ wiki/spaces/VWCA/pages/4389606 75/Leads+VWCA https://carscommerce.atlassian.net/ wiki/spaces/VWUS/pages/4390618 10/Volkswagen+Leads https://carscommerce.atlassian.net/ wiki/spaces/VOLVOCA/pages/34141 31168/Lead+Forms+Volvo+Canada 

 https://carscommerce.atlassian.net/ wiki/spaces/VUOP/pages/3662970 881/Lead+Routing+Volvo+US 


Leads are managed via OneSource. Any adjustments to ADF/XML Notifications must be managed by the client with their DDPM or updates can be made within Global Connect 

Lead Manifold 

When testing these brands that are using LM, we must verify that LM is processing the lead correctly and Success is shown on the individual lead(s) tested. 

## Dashboards and Reports 

We utilize one central Salesforce dashboard for tasks 

From this dashboard, we can see the following reports 

It is from these reports that we determine the sites that are ready for testing in both the Prelaunch and PGL environments. 

## Testing 

 1. Identify the Service Task for the Project and assign in your name 2. Open the ST, WR, and Main Account 3. Identify the main POC a. Should be listed in the WR, but you can also verify by looking at Activity and Identifying the individual present on emails from the PM 4. Send an Alert email to the main POC letting them you will be testing the lead forms on the website email templates can be found here 

 https://carscommerce.atlassian.net/wiki/spaces/CSD/pages/1214251186/Lead+Manifold+Plug in 

https://carscommerce.lightning.force.com/lightning/r/Dashboard/01Z5b000000y1IuEAI/view 

 View Report (Classic Site's Set Up/Test Lead Forms) for "Classic Site's Set Up/Test Lead Forms" View Report (Prelaunch Forms To Test) for "Prelaunch Forms To Test" View Report (Prelaunch Forms to Confirm) for "Prelaunch Forms to Confirm" View Report (PGL Forms To Test) for "PGL Forms To Test" View Report (PGL Forms to Confirm) for "PGL Forms to Confirm" 


 5. Pull up the testing template for the brand you are testing and make a copy name this new document the same name as the SF Account a. Each Brand has a folder which containing the testing templates https://drive.google.co m/drive/folders/1EYZktIsoNfDrItGwWxwmd93VQEYumPSq Connect your Google accoun t 6. Open the site and examine leads present on the front end. a. Not all forms will be present on the site so you can delete any from the testing sheet that will not be in use 7. Log in to the back end of the website and review what is present a. Shift plugin Does the brand require it, and if so, is it activated and does it have the correct dealer code? b. Lead Manifold Does the brand require it and are all forms mapped? Additionally, are ADFs appropriately disabled? 8. Open Forms from the left side menu and then open Notifications for your first form a. It will show ADF Notifications and Text Notifications b. Open each and copy the email that is present and paste it into your testing sheet c. Repeat this for all active forms on the website 9. You may now begin testing each form using the names present on the testing sheet a. You must use different names and email addresses on each form. The client CRM will list all test leads as one submission if the same name and email is used more than once 

10. Depending on the plugin being used, you will need to verify if it has processed correctly a. Shift determine if a Shift Lead ID number is present on the submission b. Lead Manifold Determine that a Success message was generated after the lead was submitted c. If either of these fail to generate, see Troubleshooting section 

11. Once all forms have been tested you will need to send a confirmation email to the POC email templates are found here 

12. Move Service Task cycle to appropriate next step after email is sent a. Prelaunch move to Cycle 3 b. PGL move to Cycle 2 c. This will move the tasks to the respective Confirmation report 

 https://docs.google.com/document/d/1xBNvrEiUG9_LVzzuop82xKnKAC2hvmJyfxgrHmt6zE/edit?pli=1# 

 https://docs.google.com/document/d/1xBNvrEiUG9_LVzzuop82xKnKAC2hvmJyfxgrHmt6zE/edit?pli=1# 


13. Confirmations a. Once the client confirms receipt of all leads for either environment, mark the Service Task Complete 

## Troubleshooting 

## Setting up Lead Forms 

 1. Once website project is in the Stage ‘In Development/Contentʼ send email to POC to confirm CRM and Text Notification addresses for leads using 2. Follow Production Development Confluence space here 3. Once forms are set up, you can set up to test the leads on the dev link 

 https://carscommerce.atlassian.net/wiki/spaces/CD/pages/531890867/Troubleshooting+Forms 

 this email template Setting up Lead Forms 


