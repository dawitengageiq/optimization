@extends('advertiser.master')

@section('content')
<form class="form-inline" role="form">
    <!-- DATE RANGE -->
    <!-- <div class="form-group">
      <label for="email">DATE RANGE</label>
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Start Date" name="q">
        <div class="input-group-btn">
          <button class="btn btn-default" type="submit">
          <i class="fa fa-calendar" aria-hidden="true"></i>
        </div>
        <input type="text" class="form-control" placeholder="End Date" name="q">
        <div class="input-group-btn">
          <button class="btn btn-default" type="submit">
          <i class="fa fa-calendar" aria-hidden="true"></i>
        </div>
      </div>
    </div> -->
    <!-- FILTER -->
    <!-- <div class="form-group">
      <label for="filter">PREDEFINED FILTERS</label>
      <select class="form-control" id="filter">
        <option>OPTION 1</option>
        <option>OPTION 2</option>
      </select>
    </div> -->
    <!-- SUBMIT -->
    <!-- <button type="submit" class="btn btn-default btn-submit">Submit</button> -->
    <!-- CAMPAIGN -->
    <!-- <div class="form-group">
      <label for="filter">CAMPAIGN</label>
      <select class="form-control" id="filter">
        <option>OPTION 1</option>
        <option>OPTION 2</option>
      </select>
    </div> -->
    <div class="form-group pull-right">
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Search..." name="search">
        <div class="input-group-btn">
          <button class="btn btn-default btn-submit" type="submit"><i class="glyphicon glyphicon-search"></i></button>
        </div>
      </div>
    </div>
</form>

<!-- REVENUE -->
<div class="container">
    <!-- REVENUE MODAL -->
    <div id="modal-revenue" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <!-- <h4 class="modal-title">Sample Title - OurCuteBaby.com</h4><br> -->
            <form class="form-inline" role="form">
              <div class="form-group">
                <label for="email">DATE RANGE</label>
                <div class="input-group">
                  <input type="text" class="form-control" placeholder="Start Date" name="q">
                  <div class="input-group-btn">
                    <button class="btn btn-default" type="submit">
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                  </div>
                  <input type="text" class="form-control" placeholder="End Date" name="q">
                  <div class="input-group-btn">
                    <button class="btn btn-default" type="submit">
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
              <!-- FILTER -->
              <div class="form-group">
                <label for="filter">PREDEFINED FILTERS</label>
                <select class="form-control" id="filter">
                  <option>Today</option>
                  <option>Week to Date</option>
                  <option>Month to Date</option>
                  <option>Year to Date</option>
                </select>
              </div>
              <!-- SUBMIT -->
              <button type="submit" class="btn btn-default btn-submit">GO</button>
            </form>
          </div>
          <div class="modal-body">
            <table class="table table-hover">
              <thead class="sub-revenue">
                <tr>
                  <!-- <th class="column-header">Date</th> -->
                  <th class="column-header">Website</th>
                  <th class="column-header">Leads</th>
                  <th class="column-header">Revenue</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="">GetPaidToTry.com</td>
                  <td class="amount leads">250</td>
                  <td class="amount">$5.80</td>
                </tr>
                <tr>
                  <td class="">SmartMoney.com</td>
                  <td class="amount leads">260</td>
                  <td class="amount">$11.20</td>
                </tr>
                <tr>
                  <td class="">LinkedIn.com</td>
                  <td class="amount leads">270</td>
                  <td class="amount">$20.80</td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div> -->
        </div>
      </div>
    </div>

    <!-- <h1>REG Path Revenue</h1>
    <table class="table table-hover">
      <thead>
        <tr>
          <th class="column-header revenue">SITES</th>
          <th class="column-header revenue">TOTAL LEADS</th>
          <th class="column-header revenue">TOTAL REVENUES</th>
        </tr>
      </thead>
      <tbody>
        <tr class="row-button" data-toggle="modal" data-target="#modal-revenue">
          <td class=" sites">SAMPLE DATA</td>
          <td class="amount total-leads">$5.80</td>
          <td class="amount total-revenue">$15.80</td>
      </tbody>
    </table> -->
</div>

<!-- HOST AND POSTED DEALS -->
<div class="container">
    <h1>Host and Posted Deals</h1>
    <table class="table table-hover">
      <thead>
        <tr>
          <th class="column-header deals">CAMPAIGN</th>
          <th class="column-header deals">LEADS</th>
          <th class="column-header deals">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        <tr class="row-button" data-toggle="modal" data-target="#modal-revenue">
          <td class="row-button stats-col-campaign">Fusion Cash</td>
          <td class="amount stats-col-leads">510</td>
          <td class="amount stats-col-total">$15.80</td>
        </tr>
        <tr class="row-button" data-toggle="modal" data-target="#modal-revenue">
          <td class="row-button stats-col-campaign">Toluna</td>
          <td class="amount stats-col-leads">520</td>
          <td class="amount stats-col-total">$25.80</td>
        </tr>
        <tr class="row-button" data-toggle="modal" data-target="#modal-revenue">
          <td class="row-button stats-col-campaign">Inbox's Pay</td>
          <td class="amount stats-col-leads">530</td>
          <td class="amount stats-col-total">$65.20</td>
        </tr>
        <tr class="row-button" data-toggle="modal" data-target="#modal-revenue">
          <td class="row-button stats-col-campaign">Survey4Bucks</td>
          <td class="amount stats-col-leads">540</td>
          <td class="amount stats-col-total">$30.80</td>
        </tr>
        <tr class="row-button" data-toggle="modal" data-target="#modal-revenue">
          <td class="row-button stats-col-campaign">Vindale Research</td>
          <td class="amount stats-col-leads">550</td>
          <td class="amount stats-col-total">$65.20</td>
        </tr>
      </tbody>
    </table>
</div>

<!-- PAGINATION -->
<ul class="pagination pagination-revenue pull-right">
	<li><a href="#" class="next-page"><i class="fa fa-chevron-left" aria-hidden="true"></i></a></li>
	<li><a href="#">1</a></li>
	<li class="active"><a href="#">2</a></li>
	<li><a href="#">3</a></li>
	<li><a href="#">4</a></li>
	<li><a href="#">5</a></li>
	<li><a href="#" class="next-page"><i class="fa fa-chevron-right" aria-hidden="true"></i></a></li>
</ul>
@stop