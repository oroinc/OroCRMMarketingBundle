# OroMarketingActivityBundle

OroMarketingActivityBundle adds Marketing Activity entity that represents events data of the marketing campaigns in Oro applications and allows management console administrators to enable and disable this feature in system configuration UI.

The bundle provides UI for users to see summary events report on the marketing campaign view pages, allows to see detail event lists on the Tracking Website and Email Campaign view pages, and enables users to create reports based on the marketing activities data.

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
