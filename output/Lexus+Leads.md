‌

| **Question**                                                                                                                                                                                                                                                                                                                                                         | **Answer**                                                                                                                                                                                                                                                                                                                                                        |
| -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Where do these forms map through? ** _Do they use Shift, Unite Digital, Autodata, etc?_                                                                                                                                                                                                                                                                            | Shift New and CPO leads are sent to Shift Only; different Shift IDs are used for Desktop and Mobile. Used leads are sent to both Shift and the Dealer; different Shift IDs are used for Desktop and Mobile. Other Forms, not specific to a vehicle for sale All leads are sent to both Shift and the Dealer; different Shift IDs are used for Desktop and Mobile. |
| **Does this brand route credit apps through the OEM?** _If yes, please provide that mapping. Does this brand capture credit app info and how is that passed? Shift and dealer, Autodata only, etc_                                                                                                                                                                   | Direct to dealer                                                                                                                                                                                                                                                                                                                                                  |
| **What are the form testing requirements?** _What are their specific naming conventions or testing rules when we are testing leads or a new form. If there are not specific form testing requirements, please put “No form testing requirements.”_                                                                                                                   | None                                                                                                                                                                                                                                                                                                                                                              |
| **Does this brand have any custom forms besides the core 1-16?** if yes, please provide the form title, provider ID, and mapping. _All sites go through development with the core #1-16 forms (Contact Us, Get E-Price, etc), does this brand have any specific forms that are required past these core 16? If so, what are they and how are they used on the site?_ | Unlock Price- This form maps to 147 - desktop and 148 - mobile.                                                                                                                                                                                                                                                                                                   |
| Schedule Your Test Drive- This form maps to 147 - desktop and 148 - mobile.                                                                                                                                                                                                                                                                                          |
| **What is the mapping ID and mapping type for each form?** _For example, are the forms mapped as shift and dealer? Dealer only?_                                                                                                                                                                                                                                     | List                                                                                                                                                                                                                                                                                                                                                              |

<truncated 4469 bytes>
Leads on Desktop | 163 | Shift and dealer |
| Leads on Mobile | 164 | Shift and dealer |

‌

Value Trade In (13)

| **Circumstances** | **Shift Provider ID** | **Send To**      |
| ----------------- | --------------------- | ---------------- |
| Leads on Desktop  | 165                   | Shift and dealer |
| Leads on Mobile   | 166                   | Shift and dealer |

‌

Lock This Lease (14)

| **Circumstances**            | **Shift Provider ID** | **Send To**      |
| ---------------------------- | --------------------- | ---------------- |
| New and CPO Leads on Desktop | 155                   | Shift Only       |
| Used Leads on Desktop        | 155                   | Shift and dealer |
| New and CPO Leads on Mobile  | 156                   | Shift Only       |
| Used Leads on Mobile         | 156                   | Shift and dealer |

‌

Lock This Payment (15)

| **Circumstances**            | **Shift Provider ID** | **Send To**      |
| ---------------------------- | --------------------- | ---------------- |
| New and CPO Leads on Desktop | 155                   | Shift Only       |
| Used Leads on Desktop        | 155                   | Shift and dealer |
| New and CPO Leads on Mobile  | 156                   | Shift Only       |
| Used Leads on Mobile         | 156                   | Shift and dealer |

‌

Ask a Question (16)

| **Circumstances**            | **Shift Provider ID** | **Send To**      |
| ---------------------------- | --------------------- | ---------------- |
| New and CPO Leads on Desktop | 147                   | Shift Only       |
| Used Leads on Desktop        | 147                   | Shift and dealer |
| New and CPO Leads on Mobile  | 148                   | Shift Only       |
| Used Leads on Mobile         | 148                   | Shift and dealer |

‌

Schedule Your Test Drive

| **Circumstances** | **Shift Provider ID** | **Send To**      |
| ----------------- | --------------------- | ---------------- |
| Leads on Desktop  | 147                   | Shift and dealer |
| Leads on Mobile   | 148                   | Shift and dealer |

‌

### **Online Shopper Leads**

Urban Science's system (the system Shift Digital utilizes for Lexus leads) can flag test leads if we are utilizing the same name repeatedly  IE: Patrick DealerInspire. If there is an issue with submitting leads to the dealership, please try a different variable.

**For Monogram Lexus Dealers:**

The Unlock Dealer Price, Estimate Payments and Contact Dealer CTAs on Monogram site route to the Monogram microsite and are sent by Lexus to the CRM. Dealer Inspire does not handle any lead routing for these leads.

‌
