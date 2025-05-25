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

$analytics .= <<<AMP_ANALYTICS
<amp-analytics type="googleanalytics" id="analytics1">
<script type="application/json">
{
  "vars": {
    "account": "{$ua[1]}"
  },
  "triggers": {
    "trackPageviewWithAmpdocUrl": {
      "on": "visible",
      "request": "pageview",
      "vars": {
        "title": "{$amptitle}",
        "ampdocUrl": "{$amplink}"
      }
    }
  }
}
</script>
</amp-analytics>
AMP_ANALYTICS;
