<?php

namespace GaShowStats
{
  class GaShowStatsController extends \WpMvc\BaseController
  {
    public function index()
    {
      global $current_site;
      global $current_blog;

      $site = \WpMvc\Site::find( $current_site->id );

      ini_set('display_errors',1);
      error_reporting(E_ALL);

      require_once 'google-api-php-client/src/apiClient.php';
      require_once 'google-api-php-client/src/contrib/apiAnalyticsService.php';
      #session_start();

      $client = new \apiClient();
      $client->setApplicationName("Google Analytics PHP Starter Application");

      // Visit https://code.google.com/apis/console?api=analytics to generate your
      // client id, client secret, and to register your redirect uri.
      $client->setClientId('707664871246-fqku20umkv8v54ofcf9jpmntmrip7g17.apps.googleusercontent.com');
      $client->setClientSecret('nYe1sT2TuyoB-SaOOSMDNjky');
      $client->setRedirectUri('http://blogg.dt.se/wp-admin/network/settings.php?page=ga_show_stats');
      #$client->setDeveloperKey('insert_your_developer_key');

      $service = new \apiAnalyticsService($client);

      if ($site->sitemeta->ga_secret->meta_value != '' && $site->sitemeta->ga_access_token->meta_value == '') {
        $client->authenticate();
        $site->sitemeta->ga_access_token->meta_value = $client->getAccessToken();
        $site->save();
        #$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        #header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
      }

      if ($site->sitemeta->ga_access_token->meta_value != '') {
        $client->setAccessToken($site->sitemeta->ga_access_token->meta_value);
      }

      if ($site->sitemeta->ga_access_token->meta_value != '') {
        $client->setUseObjects(true);

        $analytics = new \apiAnalyticsService($client);

        $optParams = array(
          'dimensions' => 'ga:pageTitle,ga:hostname,ga:pagePath',
          'sort' => '-ga:pageViews',
          'max-results' => 10,
          'filters' => "ga:pagePath=~{$current_blog->path}"
        );

        $array_hits = $service->data_ga->get(
          'ga:17287970',
          date('Y-m-d', strtotime('-1 week', strtotime('midnight'))),
          date('Y-m-d', strtotime('midnight')),
          'ga:pageViews',
          $optParams
        );

        global $hits_table;

        $hits_table = '<h4>Populäraste innehållet</h4><ol>';

        foreach ($array_hits->rows as $row) {
          $title = $row[0];
          $host = $row[1];
          $link = $row[2];

          $pageviews = intval($row[2]);

          $hits_table .= '<li><a href="http://' . $host . $link . '">' . $title . '</a></li>';
        }

        $hits_table .= '</ol>';

        $optParams = array(
          'dimensions' => 'ga:day,ga:month',
          'filters' => "ga:pagePath=~{$current_blog->path}"
        );

        $array_week = $service->data_ga->get(
          'ga:17287970',
          date('Y-m-d', strtotime('-1 week', strtotime('midnight'))),
          date('Y-m-d', strtotime('midnight')),
          'ga:pageViews,ga:visitors',
          $optParams
        );

        $highchart_week_categories  = array();
        $highchart_week_pageviews   = array();
        $highchart_week_visitors    = array();

        foreach ($array_week->rows as $row) {
          $day = intval($row[0]);
          $month = intval($row[1]);
          $date = "{$day}/{$month}";

          $pageviews = intval($row[2]);
          $visitors = intval($row[3]);

          $highchart_week_pageviews[]   = $pageviews;
          $highchart_week_visitors[]    = $visitors;
          $highchart_week_categories[]  = $date;
        }

        $js_array_pageviews = 'var array_pageviews = new Array();';
        foreach ( $highchart_week_pageviews as $pageview ) {
          $js_array_pageviews .= "array_pageviews.push({$pageview});";
        }

        $js_array_visitors = 'var array_visitors = new Array();';
        foreach ( $highchart_week_visitors as $visitor ) {
          $js_array_visitors .= "array_visitors.push({$visitor});";
        }

        $js_array_pageviews_dates = 'var array_pageviews_dates = new Array();';
        foreach ( $highchart_week_categories as $date ) {
          $js_array_pageviews_dates .= "array_pageviews_dates.push('{$date}');";
        }

        global $js_week;

        $js_week = <<<js

        <script type="text/javascript">
          {$js_array_pageviews_dates}
          {$js_array_pageviews}
          {$js_array_visitors}

          array_pageviews.pop();
          array_visitors.pop();
          array_pageviews_dates.pop();

          var highcharts_data_pageviews = {
            name: 'Sidvisningar',
            data: array_pageviews
          };

          var highcharts_data_visitors = {
            name: 'Besök',
            data: array_visitors
          };

          var options_week = {
            chart: {
              renderTo: 'analytics_week',
              defaultSeriesType: 'line'
            },
            title: {
              text: 'Statistik en vecka bakåt'
            },
            xAxis: {
              categories: array_pageviews_dates
            },
            yAxis: {
              title: {
                text: ''
              }
            },
            series: [
              highcharts_data_pageviews,
              highcharts_data_visitors
            ]
          };

          jQuery(function() {
            setTimeout(function() {
              var week_chart = new Highcharts.Chart(options_week);
            }, 1000);
          });
        </script>

js;

        $optParams = array(
          'dimensions' => 'ga:week',
          'filters' => "ga:pagePath=~{$current_blog->path}"
        );

        $array_three_months = $service->data_ga->get(
          'ga:17287970',
          date('Y-m-d', strtotime('-3 months', strtotime('midnight'))),
          date('Y-m-d', strtotime('midnight')),
          'ga:pageViews,ga:visitors',
          $optParams
        );

        $highchart_three_months_categories  = array();
        $highchart_three_months_pageviews   = array();
        $highchart_three_months_visitors    = array();

        foreach ($array_three_months->rows as $row) {
          $week = intval($row[0]);
          $date = "v.{$week}";

          $pageviews = intval($row[1]);
          $visitors = intval($row[2]);

          $highchart_three_months_pageviews[]   = $pageviews;
          $highchart_three_months_visitors[]    = $visitors;
          $highchart_three_months_categories[]  = $date;
        }

        $js_array_pageviews = 'var array_pageviews = new Array();';
        foreach ( $highchart_three_months_pageviews as $pageview ) {
          $js_array_pageviews .= "array_pageviews.push({$pageview});";
        }

        $js_array_visitors = 'var array_visitors = new Array();';
        foreach ( $highchart_three_months_visitors as $visitor ) {
          $js_array_visitors .= "array_visitors.push({$visitor});";
        }

        $js_array_pageviews_dates = 'var array_pageviews_dates = new Array();';
        foreach ( $highchart_three_months_categories as $date ) {
          $js_array_pageviews_dates .= "array_pageviews_dates.push('{$date}');";
        }

        global $js_three_months;

        $js_three_months = <<<js

        <script type="text/javascript">
          {$js_array_pageviews_dates}
          {$js_array_pageviews}
          {$js_array_visitors}

          array_pageviews.pop();
          array_visitors.pop();
          array_pageviews_dates.pop();

          var highcharts_data_pageviews = {
            name: 'Sidvisningar',
            data: array_pageviews
          };

          var highcharts_data_visitors = {
            name: 'Besök',
            data: array_visitors
          };

          var options_three_months = {
            chart: {
              renderTo: 'analytics_three_months',
              defaultSeriesType: 'line'
            },
            title: {
              text: 'Statistik tre månader bakåt'
            },
            xAxis: {
              categories: array_pageviews_dates
            },
            yAxis: {
              title: {
                text: ''
              }
            },
            series: [
              highcharts_data_pageviews,
              highcharts_data_visitors
            ]
          };

          jQuery(function() {
            setTimeout(function() {
              var three_months_chart = new Highcharts.Chart(options_three_months);
            }, 1000);
          });
        </script>

js;

        /*$client_visitors = $accessToken->getHttpClient( $oauthOptions );
        $client_visitors->resetParameters();

        $parameters_visitors = array(
          'ids'         => 'ga:17287970',
          'dimensions'  => 'ga:week',
          'metrics'     => 'ga:pageViews,ga:visitors',
          'start-date'  => date('Y-m-d', strtotime('-3 months', strtotime('midnight'))),
          'end-date'    => date('Y-m-d', strtotime('midnight')),
          'alt'         => 'json'
        );

        $client_visitors->setUri( 'https://www.google.com/analytics/feeds/data' );

        $client_visitors->setParameterGet( $parameters_visitors );

        $client_visitors->setMethod( \Zend_Http_Client::GET );

        $response_visitors = $client_visitors->request();

        $array_visitors = json_decode( \Zend_Http_Response::extractBody( $response_visitors ), true );

        $highchart_data_pageviews         = array();
        $highchart_categories_pageviews   = array();

        foreach ($array_visitors['feed']['entry'] as $entry) {
          $week = intval($entry['dxp$dimension'][0]['value']);
          $date = "v.{$week}";

          $visitors = intval($entry['dxp$metric'][0]['value']);

          $highchart_data_visitors[] = $visitors;
          $highchart_categories_visitors[] = $date;
        }

        $js_array_visitors = 'var array_visitors = new Array();';
        foreach ( $highchart_data_visitors as $visitor ) {
          $js_array_visitors .= "array_visitors.push({$visitor});";
        }

        $js_array_visitors_dates = 'var array_visitors_dates = new Array();';
        foreach ( $highchart_categories_visitors as $date ) {
          $js_array_visitors_dates .= "array_visitors_dates.push('{$date}');";
        }

        global $js_visitors;

        $js_visitors = <<<js

        <script type="text/javascript">
          {$js_array_visitors_dates}
          {$js_array_visitors}

          array_visitors.pop();
          array_visitors_dates.pop();

          var highcharts_data_visitors = {
            name: 'Sidvisningar',
            data: array_visitors
          };

          var options_visitors = {
            chart: {
              renderTo: 'analytics_visitors',
              defaultSeriesType: 'line'
            },
            title: {
              text: 'Statistik tre månader bakåt'
            },
            xAxis: {
              categories: array_visitors_dates
            },
            yAxis: {
              title: {
                text: ''
              }
            },
            series: [
              highcharts_data_visitors
            ]
          };

          var visitors_chart = new Highcharts.Chart(options_visitors);
        </script>

js;
*/
      } else if (isset($_GET['auth']) && $_GET['auth'] == 1) {
        $authUrl = $client->createAuthUrl();
        print "<a class='login' href='$authUrl'>Connect Me!</a>";
      }

      $this->render( $this, "index" );
    }
  }
}
