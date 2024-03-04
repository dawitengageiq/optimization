<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <title>Engage IQ</title>
  </head>
  <body>
    <div class="row">
      <div class="col-md-10 mx-auto">
        <div class="">
          <img class="logo" src="{{ URL::asset('images/logos/engageiq-logo.png') }}" alt="EngageIQ Icon" title="EngageIQ">
        </div>
        <h2>{{$campaign->publisher_name != '' ? $campaign->publisher_name : $campaign->name}} Posting Instructions</h2>
        <table class="table table-bordered">
          <tbody>
            @if(isset($creative))
            <tr>
              <th width="20%">Ad Image</th>
              <td>
                <img class="logo" src="{{ URL::asset($creative->image) }}">
              </td>
            </tr>
            <tr>
              <th>Adcopy</th>
              <td>
                {!! $creative->description !!}
              </td>
            </tr>
            @endif
            <tr>
              <th>Posting URL</th>
              <td>
                <?php 
                if($json['form']['url'] == 'lead_reactor') $url = url('sendLead/');
                else if($json['form']['url'] == 'lead_filter') $url = config('constants.LEAD_FILTER_URL');
                else $url = $json['form']['custom_url'];
                echo $url;
                ?>
              </td>
            </tr>
            @if(isset($config->post_method))
            <tr>
              <th>Method</th>
              <td>{{$config->post_method}}</td>
            </tr>
            @endif
            <tr>
              <th>Fields</th>
              <td>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th scope="col">Field Name</th>
                      <th scope="col">Meaning/Sample Data</th>
                      <th scope="col">Required</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($json['fields'] as $field)
                    <?php 
                      $isRequired = 'Yes';
                      if(isset($field['validation']) && $field['validation']['required'] == 'false') $isRequired = 'Optional';
                    ?>
                    <tr>
                      <td>{{$field['name']}}</td>
                      <td>
                        <?php
                          if(in_array($field['type'], ['dropdown', 'checkbox', 'radio'])) {
                            echo '<b>'.$field['label'].'</b>';
                        ?> 
                          <table class="table table-bordered table-sm">
                            <thead>
                              <tr>
                                <th scope="col">Value</th>
                                <th scope="col">Display</th>
                                <th scope="col">Accepted</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php 
                              $c = 0;
                              ?>
                              @foreach($field['options']['values'] as $field_val)
                              <?php 
                                $accepted = 'Yes';
                                if($field['has_accepts'] == 'true' && isset($field['accepted_options']) && !in_array($field_val, $field['accepted_options'])) $accepted = 'No';
                              ?>
                              <tr>
                                <td>{{$field_val}}</td>
                                <td>{{$field['options']['displays'][$c]}}</td>
                                <td>{{$accepted}}</td>
                              </tr>
                              <?php $c++ ?>
                              @endforeach
                            </tbody>
                          </table>
                        <?php
                          }
                          else {
                            $value = $field['value'];
                            if($value == '[VALUE_CREATIVE_ID]') $value = isset($creative->id) ? $creative->id : '';
                            echo $value;
                          }
                        ?>
                      </td>
                      <td>{{$isRequired }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </td>
            </tr>
            <tr>
              <th>Response</th>
              <td>
                <table class="table table-bordered table-sm">
                  <tbody>
                    <tr>
                      <th>Success</th>
                      <td>{"status":"lead_received","message":"Lead received for processing","lead_id":45210}</td>
                    </tr>
                    {{-- <tr>
                      <th>Failed</th>
                      <td>{"status":"email_empty","message":"Email empty"}</td>
                    </tr> --}}
                  </tbody>
                </table>
              </td>
            </tr>
            <tr>
              <th>Example</th>
              <td>
                <?php 
                  $url;
                  $params = [];
                  foreach($sample['fields'] as $field) {
                    $value = $field['value'];
                    if($value == '[VALUE_CREATIVE_ID]') $value = isset($creative->id) ? $creative->id : '';
                    if(in_array($field['type'], ['dropdown', 'checkbox', 'radio'])) {
                      if($field['has_accepts'] == 'true') {
                        $value = $field['accepted_options'][0];
                      }else {
                        $value = $field['options']['values'][0];
                      }
                    }
                    $params[$field['name']] = $value;
                    // $params .= $field['name'].'='.$value.'&';

                  }
                  $params = http_build_query($params);
                ?>
                <a href="{{$url.'?'.$params}}">{{$url.'?'.$params}}</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </body>
</html>