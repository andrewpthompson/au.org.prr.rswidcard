<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="{$extBaseUrl}packages/bootswatch/lumen/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="{$extBaseUrl}css/extern/rswdata.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>

    <title>{$pageTitle}</title>
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
          <h1>{$mainHeading}</h1>
        </div>
        {if $errMsg}
          <div class="alert alert-warning rsw-header">{$errMsg}</div>
          
        {else}
          <div class="rsw-header">
            <h2>{$fullName}</h2>
            <p>All data is confidential</p>
          </div>
          
          <div id="accordion">
            {if $approvals}
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
                      {if $approvals|count == 0}
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
                                <td>{$approval["Approval:label"]}</td>
                                {* <td>{$approval.Date|date_format:$dateFormat}</td> *}
                                <td>{$approval.Other_detail}</td>
                              </tr>
                            {/foreach}
                          </table>
                        </div>
                      {/if}
                  </div>
                </div>
              </div>
            {/if}
            
            {if $trgassessments}
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
                      {if $trgassessments|count == 0}
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
                                {* <td>{$trgassessment["Record_type:label"]}</td> *}
                                <td>{$trgassessment["Assessment_or_training_name:label"]}</td>
                                <td>{$trgassessment.Date|date_format:$dateFormat}</td>
                                <td>{$trgassessment.Other_detail}</td>
                              </tr>
                            {/foreach}
                        </table>
                        </div>
                      {/if}
                  </div>
                </div>
              </div>
            {/if}
            
            {if $extQuals}
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
                      {if $extQuals|count == 0}
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
                                <td>{$extQual["Name_of_training_qualification:label"]}</td>
                                {* <td>{$extQual.Date|date_format:$dateFormat}</td> *}
                                <td>{$extQual.Expiry_date|date_format:$dateFormat}</td>
                                <td>{$extQual.Other_detail}</td>
                              </tr>
                            {/foreach}
                          </table>
                        </div>
                      {/if}
                  </div>
                </div>
              </div>
            {/if}
            
            {if $health}
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
                      {if $health|count == 0}
                        <p>No record</p>
                      {else}
                        <p><b>Risk category:</b> {$health["Category:label"]}</p>
                        <p><b>Expiry date:</b> {$health.Expiry_date|date_format:$dateFormat}</p>
                        <p><b>Fitness for duty categorisation:</b> {$health["Health_assessment_result:label"]}</p>
                        {if !empty($health["Conditions:label"])}
                        <p><b>Conditions:</b><br />
                          {foreach from=$health["Conditions:label"] item=condition}
                            {$condition}<br />
                          {/foreach}
                        {/if}</p>
                        {if !empty($health.Other_detail)}<p><b>Other detail:</b> {$health.Other_detail}</p>{/if}
                      {/if}
                  </div>
                </div>
              </div>
            {/if}
            
            {if $membership}
              <div class="card">
                <div class="card-header" id="headingFive">
                  <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                      Membership
                    </button>
                  </h5>
                </div>
                <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#accordion">
                  <div class="card-body">
                    {if $membership|count == 0}
                      <p>No current membership record</p>
                    {else}
                      {assign var="key_membership_type" value="membership_type_id.name"}
                      {assign var="key_status" value="status_id.name"}
                      <p><b>Joining date:</b> {$membership.join_date|date_format:$dateFormat}</p>
                      <p><b>Membership type:</b> {$membership["membership_type_id:label"]}</p>
                      <p><b>Membership status:</b> {$membership["status_id:label"]}</p>
                      <!--<p><b>Current membership period started:</b> {$membership.start_date|date_format:$dateFormat}</p>-->
                      <p><b>Membership expires:</b> {$membership.end_date|date_format:$dateFormat}</p>
                      <p>Note: The joining date may indicate the start of continous membership in the current membership category, rather than the date that the member first joined.</p>
                    {/if}
                  </div>
                </div>
              </div>
            {/if}

            {if $licenceAndMedical}
              <div class="card">
                <div class="card-header" id="headingSix">
                  <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                      Licence and Medical Certificate
                    </button>
                  </h5>          
                </div>
                <div id="collapseSix" class="collapse" aria-labelledby="headingSix" data-parent="#accordion">
                  <div class="card-body">
                    {if $licenceAndMedical|count == 0}
                      <p>No record</p>
                    {else}
                      <p><b>Aviation reference number:</b> {$licenceAndMedical["Licence_and_Medical_Certificate.Aviation_Reference_Number"]}</p>
                      <p><b>Licence Type:</b><br />
                        {foreach from=$licenceAndMedical["Licence_and_Medical_Certificate.Licence_Type"] item=licence}
                          {$licence}<br />
                        {/foreach}</p>
                      <p><b>Ratings:</b> {$licenceAndMedical["Licence_and_Medical_Certificate.Ratings"]}</p>
                      <p><b>Aeroplane flight review expires:</b> {$licenceAndMedical["$fields.Aeroplane_Flight_Review_Expires"]|date_format:$dateFormat}</p>
                      <p><b>Medical class:</b> {if $licenceAndMedical["Licence_and_Medical_Certificate.Medical_Class"] > 0}{$licenceAndMedical["Licence_and_Medical_Certificate.Medical_Class"]}{/if}</p>
                      <p><b>Medical expiry date:</b> {$licenceAndMedical["Licence_and_Medical_Certificate.Medical_Expiry_Date"]|date_format:$dateFormat}</p>
                      <p><b>Last flight:</b> {$licenceAndMedical["Licence_and_Medical_Certificate.Last_Flight"]|date_format:$dateFormat}</p>
                    {/if}
                  </div>
                </div>
              </div>
            {/if}
          </div>
        {/if}
        
        {include file="$extTplPath/extern/help.tpl"}
      {/if}
    </div>
    
    <script src="{$extBaseUrl}packages/jquery/js/jquery-3.7.1.min.js"></script>
    <script src="{$extBaseUrl}packages/bootstrap4/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
