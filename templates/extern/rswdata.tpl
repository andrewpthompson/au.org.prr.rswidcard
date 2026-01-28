<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="{$extBaseUrl}packages/bootswatch/lumen/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="{$extBaseUrl}css/extern/rswdata.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>

    <title>Pichi Richi Railway Preservation Society &ndash; Railway worker data</title>
  </head>
  <body>
    <div id="rswid-main">
      {if $isCaptcha}
        <div class="rsw-header">
          <h1>Pichi Richi Railway Preservation Society Inc.</h1>
          {include file="$extTplPath/extern/recaptcha.tpl"}
          <p>Unauthorised access is prohibited</p>
        </div>
      {else}
        <div class="rsw-header">
          <h1>Railway worker data</h1>
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
                  <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    PRRPS approvals
                  </button>
                </h5>
              </div>
              <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    {if (isset($approvals.is_error) && $approvals.is_error == 1) or $approvals|count == 0}
                      <p>No record</p>
                    {else}
                      <p>The worker is approved to work in the roles that are listed below.</p>
                      <div class="table-responsive">
                        <table class="table table-sm">
                          <thead>
                            <tr>
                              <th scope="col" class="col-approval-label">Approval</th>
                              {* <th scope="col" class="col-date">Date approved</th> *}
                              <th scope="col" class="col-detail">Other detail</th>
                            </tr>
                          </thead>
                          {foreach from=$approvals item=approval}
                            <tr>
                              <td>{$approval.app_approval_name}</td>
                              {* <td>{$approval.app_date|date_format:$dateFormat}</td> *}
                              <td>{$approval.app_other_detail}</td>
                            </tr>
                          {/foreach}
                        </table>
                      </div>
                    {/if}
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-header" id="headingTwo">
                <h5 class="mb-0">
                  <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    PRRPS training and assessments
                  </button>
                </h5>
              </div>
              <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                <div class="card-body">
                    {if (isset($trgassessments.is_error) && $trgassessments.is_error == 1) or $trgassessments|count == 0}
                      <p>No record</p>
                    {else}
                      <div class="table-responsive">
                        <table class="table table-sm">
                          <thead>
                            <tr>
                              {* <th scope="col" class="col-trg-assess-type">Record type</th> *}
                              <th scope="col" class="col-assess-label">Assessment/training</th>
                              <th scope="col" class="col-date">Date</th>
                              <th scope="col" class="col-detail">Other detail</th>
                            </tr>
                          </thead>
                          {foreach from=$trgassessments item=trgassessment}
                            <tr>
                              {* <td>{$trgassessment.ass_record_type}</td> *}
                              <td>{$trgassessment.ass_trg_assess_name}</td>
                              <td>{$trgassessment.ass_date|date_format:$dateFormat}</td>
                              <td>{$trgassessment.ass_other_detail}</td>
                            </tr>
                          {/foreach}
                      </table>
                      </div>
                    {/if}
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-header" id="headingThree">
                <h5 class="mb-0">
                  <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    External training and qualifications
                  </button>
                </h5>
              </div>
              <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                <div class="card-body">
                    {if (isset($extQuals.is_error) && $extQuals.is_error == 1) or $extQuals|count == 0}
                      <p>No record</p>
                    {else}
                      <div class="table-responsive">
                        <table class="table table-sm">
                          <thead>
                            <tr>
                              <th scope="col" class="col-qual-label">Qualification/training</th>
                              {* <th scope="col" class="col-date">Date</th> *}
                              <th scope="col" class="col-date">Expiry date</th>
                              <th scope="col" class="col-detail">Other detail</th>
                            </tr>
                          </thead>
                          {foreach from=$extQuals item=extQual}
                            <tr>
                              <td>{$extQual.extqu_qualtrg_name}</td>
                              {* <td>{$extQual.extqu_date|date_format:$dateFormat}</td> *}
                              <td>{$extQual.extqu_exp_date|date_format:$dateFormat}</td>
                              <td>{$extQual.extqu_other_detail}</td>
                            </tr>
                          {/foreach}
                        </table>
                      </div>
                    {/if}
                </div>
              </div>
            </div>
            
            <div class="card">
              <div class="card-header" id="headingFour">
                <h5 class="mb-0">
                  <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    Rail safety health assessment
                  </button>
                </h5>          
              </div>
              <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
                <div class="card-body">
                    {if (isset($health.is_error) && $health.is_error == 1) or $health|count == 0}
                      <p>No record</p>
                    {else}
                      <p><b>Risk category:</b> {$health.ha_category}</p>
                      <p><b>Expiry date:</b> {$health.ha_exp_date|date_format:$dateFormat}</p>
                      <p><b>Fitness for duty categorisation:</b> {$health.ha_result}</p>
                      {if !empty($health.ha_conditions)}
                      <p><b>Conditions:</b><br />
                        {foreach from=$ha_conditions item=condition}
                          {$condition}<br />
                        {/foreach}
                      {/if}</p>
                      {if !empty($health.ha_other_detail)}<p><b>Other detail:</b> {$health.ha_other_detail}</p>{/if}
                    {/if}
                </div>
              </div>
            </div>
          </div>
        {/if}
        
        {include file="$extTplPath/extern/help.tpl"}
        <div class="rsw-header"></div><p>&copy; Copyright Pichi Richi Railway Preservation Society Inc.</p></div>
      {/if}
    </div>
    
    <script src="{$extBaseUrl}packages/jquery/js/jquery-3.3.1.min.js"></script>
    <script src="{$extBaseUrl}packages/bootstrap4/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
