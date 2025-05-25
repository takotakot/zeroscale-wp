<?php
/***
 Google Analytics for AMP

 ////// CAUTION ! //////
 This file usually does not require editing. Edit only if you have knowledge.
 This file can not be edited from the child theme editing function of Luxeritas.

 ////// 注意 ! //////
 通常、このファイルは編集不要です。知識のある方のみ編集してください。
 このファイルは、Luxeritas の子テーマ編集機能からは編集できないようにしてあります。

 ***/

$analytics .= <<<AMP_ANALYTICS4
<amp-analytics type="googleanalytics" config="https://amp.analytics-debugger.com/ga4.json" data-credentials="include">
<script type="application/json">
{
	"vars": {
		"GA4_MEASUREMENT_ID": "{$ga[1]}",
		"GA4_ENDPOINT_HOSTNAME": "www.google-analytics.com",
		"DEFAULT_PAGEVIEW_ENABLED": true,
		"GOOGLE_CONSENT_ENABLED": false,
		"WEBVITALS_TRACKING": false,
		"PERFORMANCE_TIMING_TRACKING": false,
		"SEND_DOUBLECLICK_BEACON": false
	}
}
</script>
</amp-analytics>
AMP_ANALYTICS4;
