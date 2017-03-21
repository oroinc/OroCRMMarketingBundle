OroMarketingActivityBundle
===================

Bundle responsibilities:
------------------------

Bundle provides general storage and UI for various marketing activities (e.g. email campaigns activites like click,
open etc.). Marketing activities are stored per marketing campaign. 

UI includes:

    - Marketing activites summary on marketing campaign view page
    - Marketing activites widget on entity view pages
    - Ability to create reports based on marketing activities data

Activity Types
------------

A list of predefined activity types:
- Send
- Open
- Click
- Unsubscribe
- Soft bounce
- Hard bounce

New activity types can be introduced by adding new options for type (ma_type) enum field.

Reporting
------------

Additional aggregation functions are available for marketing activity type field:
- Send Count
- Open Count
- Click Count
- Unsubscribe Count
- Soft bounce Count
- Hard bounce Count

Using these functions may be useful when statistics on several activities should be displayed on a single report grid.
