# dans-charts-for-fitbit
WordPress plugin to allow displaying of API data from Fitbit on the front end of your site in nicely formatted chart.

## Setup
* Download this plugin, using git or [download the zip file here](https://github.com/duplaja/dans-charts-for-fitbit/archive/master.zip)
* Install it, as per usual, on your WordPress site and activate it.
* Go to *Tools* > *Fitbit Chart* on your Admin Dashboard.
* Go to https://dev.fitbit.com/apps/new , and create a new Fitbit App, with the following settings.
    - **Application Name** - Your choice
    - **Description** - Your Choice
    - **Application Website** - The website you are installing this on.
    - **Organization** - Your name or business name
    - **Organization Website** - Your website or business website
    - **Terms Of Service Url** - Your website
    - **Privacy Policy Url** - Your website
    - **OAuth 2.0 Application Type** - Personal
    - **Callback URL** - Important: This is the Settings Page URL that you navigated to above. 
    - **Default Access Type** - Read-Only
* Accept the terms of service, and register your application.
* Copy the client ID, the client secret, and the callback URL to your settings page in your WordPress site (that we went to above).
* After you click "Save Changes", a link will appear.
* Click the link that says: `Click Here To Authorize / Force Reauthorization`, and log in to Fitbit when prompted.
    - Allow all permissions, if you wish.
* Your site is now fully linked to Fitbit!

## Displaying Charts on the Front-End

This plugin is shortcode based. You can see all of the arguements, and possible values, below:

Base Shortcode `[dans_fitbit_display]` (some attributes are required)

| Attribute     | Possible Values                 | Required / Default Value?                   |
|---------------|---------------------------------|---------------------------------------------|
| start_date    | Start date in yyyy-mm-dd format | No, defaults to current date if not entered |
| end_date      | End date in yyyy-mm-dd format   | No, defaults to current date if not entered |
| chart_title   | Title for your chart            | No, defaults to "Daily Steps"               |
| dataset_label | Label for your Data             | No, defaults to "Daily Steps"               |
| type      | steps (for daily steps), weight (for daily weight), distance, calories_in, calories_out | No, defaults to "steps"   |
| legend_id | any string (Specific ID for the legend of your chart, if used)                          | No, defaults to random    |
| canvas_id | any string (Specific ID for the canvas of your chart)                                   | No, defaults to random    |
| is_adf    | "yes" or "no" (Toggles on an extra legend + alternating color dots for line charts)     | No, defaults to no.       |
| graph_type | "line", "bar" (may add support for others later) | No, default is line. |
| stepped | "false" or "true" (only affects graph_type="line", stepped line graph) | No, defaults to "false" |

### Sample Shortcodes

Show weight changes from 9/4/2019 until present as a line graph, with alternating colored dots for ADF (Alternate Day Fasting)
> [dans_fitbit_display start_date='2019-09-04' chart_title='Weight Change Since 9/4/2019' dataset_label='Weight (lbs) Measured at 6am' is_adf='yes' type='weight']

Show a stepped line graph for steps per day, from 9/4/2019 until present
> [dans_fitbit_display start_date='2019-09-04' chart_title='Steps Since 9/4/2019' dataset_label='Steps Per Day' is_adf='no' type='steps' graph_type='line' stepped='true']

Show a bar graph for calories burned per day, from 9/4/2019 until present

> [dans_fitbit_display start_date='2019-09-04' chart_title='Calories Burned Since 9/4/2019' dataset_label='Daily Calories Burned' is_adf='no' type='calories_out' graph_type='bar']

## Todo / Upcoming Roadmap
* Setting for Imperial Units vs Metric (hard-coded Imperial at the moment)
* Impliment some short-term caching options, to cut down on API calls (transients)
* Expand data able to be pulled (body fat %, etc)
* Look at some combination graphs (ie, calories in and out mapped on the same graph, calories out + steps walked, etc).


## Built Using
* [Fitbit's Web API ](https://dev.fitbit.com/build/reference/web-api/)
* [ChartJS](https://www.chartjs.org/)
