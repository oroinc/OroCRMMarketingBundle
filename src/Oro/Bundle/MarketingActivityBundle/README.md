# OroMarketingActivityBundle

## Bundle Responsibilities

Bundle provides a general storage and UI for various marketing activities (e.g. email campaign activities such as click,
open, etc.). Marketing activities are stored per a marketing campaign. 

UI includes:

    - Marketing activites summary on the marketing campaign view page;
    - Marketing activites widget on the entity view pages;
    - Ability to create reports based on the marketing activities data.

## Activity Types

A list of predefined activity types:

- Send
- Open
- Click
- Unsubscribe
- Soft bounce
- Hard bounce

New activity types can be introduced by adding new options for the type (ma_type) enum field.

## Reporting

Additional aggregation functions are available for the marketing activity type field:

- Send Count
- Open Count
- Click Count
- Unsubscribe Count
- Soft bounce Count
- Hard bounce Count

These functions may be useful when displaying the statistics for several activities on a single report grid.
