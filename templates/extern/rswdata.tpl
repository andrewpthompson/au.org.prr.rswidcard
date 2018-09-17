<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="{$extBaseUrl}packages/bootswatch/lumen/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="{$extBaseUrl}css/extern/rswdata.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>

    <title>Phoenix Aero Club &ndash; Member data</title>
  </head>
  <body>
    <div id="rswid-main">
      {if $isCaptcha}
        <div class="rsw-header">
          <h1>Phoenix Aero Club</h1>
          {include file="$extTplPath/extern/recaptcha.tpl"}
          <p>Unauthorised access is prohibited</p>
        </div>
      {else}
        <div class="rsw-header">
          <h1>Member data</h1>
        </div>
        {if $errMsg}
          <div class="alert alert-warning rsw-header">{$errMsg}</div>
          
        {else}
          <div class="rsw-header">
            <h2>{$fullName}</h2>
            <p>All data is confidential</p>
          </div>
          
          <div id="accordion">
            <div class="card">
              <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                  <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    Membership
                  </button>
                </h5>
              </div>
              <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                  {if $membership.is_error eq 1}
                    <p>No record</p>
                  {else}
                    {assign var="key_membership_type" value="membership_type_id.name"}
                    {assign var="key_status" value="status_id.name"}
                    <p><b>Joining date:</b> {$membership.join_date|date_format:$dateFormat}</p>
                    <p><b>Membership type:</b> {$membership.$key_membership_type}</p>
                    <p><b>Membership status:</b> {$membership.$key_status}</p>
                    <!--<p><b>Current membership period started:</b> {$membership.start_date|date_format:$dateFormat}</p>-->
                    <p><b>Membership expires:</b> {$membership.end_date|date_format:$dateFormat}</p>
                  {/if}
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-header" id="headingTwo">
                <h5 class="mb-0">
                  <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Licence and Medical Certificate
                  </button>
                </h5>          
              </div>
              <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                <div class="card-body">
                  {if $licenceAndMedical.is_error eq 1}
                    <p>No record</p>
                  {else}
                    <p><b>Aviation reference number:</b> {$licenceAndMedical[$fields.Aviation_Reference_Number]}</p>
                    <p><b>Licence Type:</b><br />
                      {foreach from=$licenceAndMedical[$fields.Licence_Type] item=rating}
                        {$rating}<br />
                      {/foreach}</p>
                    <p><b>Ratings:</b> {$licenceAndMedical[$fields.Ratings]}</p>
                    <!--<p><b>Licence:</b> {$licenceAndMedical[$fields.Licence]}</p>-->
                    <p><b>Aeroplane flight review expires:</b> {$licenceAndMedical[$fields.Aeroplane_Flight_Review_Expires]|date_format:$dateFormat}</p>
                    <p><b>Medical class:</b> {if $licenceAndMedical[$fields.Medical_Class] gt 0}{$licenceAndMedical[$fields.Medical_Class]}{/if}</p>
                    <!--<p><b>Medical certificate:</b> {$licenceAndMedical[$fields.Medical_Certificate]}</p>-->
                    <p><b>Medical expiry date:</b> {$licenceAndMedical[$fields.Medical_Expiry_Date]|date_format:$dateFormat}</p>
                    <p><b>Last flight:</b> {$licenceAndMedical[$fields.Last_Flight]|date_format:$dateFormat}</p>
                  {/if}
                </div>
              </div>
            </div>
          </div>
        {/if}
      {/if}
    </div>
    
    <script src="{$extBaseUrl}packages/jquery/js/jquery-3.3.1.min.js"></script>
    <script src="{$extBaseUrl}packages/bootstrap4/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
