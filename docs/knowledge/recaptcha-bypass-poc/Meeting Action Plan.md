# Brainstorming Meeting Action Plan

**Meeting**: Automated Lead Form Testing Brainstorm
**Attendees**: Nate Hart, Bob Hipps, Ryan Vandehey
**Goal**: Design a proactive, automated system for testing lead forms on live websites, overcoming ReCaptcha and Security blockers.

## Agenda

1.  **Review Current Workflow & Constraints (5 min)**
    - Briefly walk through the manual process (Lead Form Delivery doc).
    - Confirm the primary blocker: **ReCaptcha on Live Sites**.
    - Confirm secondary constraints: CRM pollution, Unique data requirements.

2.  **Problem Framing (10 min)**
    - **"How Might We..."**
      - ...verify lead delivery without submitting a user-facing form?
      - ...bypass ReCaptcha securely for authorized testing bots?
      - ...intercept leads _before_ the CRM but _after_ the website?

3.  **Ideation Session (25 min)**
    - _Technique: Constraint Mapping_ - List every constraint and challenge it.
    - _Technique: Role Playing_ - Think like a Hacker vs. a Developer.
    - _Technique: Reversal_ - What if we didn't use the form? What if the CRM tested itself?
    - **Focus Areas**:
      - **Bypassing UI**: Direct API manipulation?
      - **Bypassing Security**: Whitelisted IPs / Headers / Magic Tokens?
      - **Mocking**: Testing up to the edge of the CRM?

4.  ** Next Steps (5 min)**
    - Select top 2-3 viable approaches.
    - Assign owner to POC (Proof of Concept) the bypass method.
    - Schedule follow-up.

## Preparation Checklist

- [ ] Share "Lead Form Delivery Workflow Summary" with group.
- [ ] Have "Automation Ideas" list ready to spark conversation.
- [ ] Bring technical details on ReCaptcha implementation (v2/v3? Invisible?).
