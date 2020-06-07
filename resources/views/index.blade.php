@include('header')

<div class="container-fluid">

<div class="row">
<div class="col-md-2"></div>
<div class="col-md-8">
@if (session('message'))
    <div class="alert alert-success"><span class="glyphicon glyphicon-ok"></span> {{ session('message') }}</div>
    <script>dataLayer.push({'event': 'subscribed'});</script>
@elseif (session('error'))
    <div class="alert alert-danger"><span class="glyphicon glyphicon-remove"></span> {{ session('error') }}</div>
    <script>dataLayer.push({'event': 'unsubscribed'});</script>
@else
    <p>Sleduj EIA je projekt, ktorý umožňuje posielať notifikácie emailom o projektoch s dopadom na vaše životné prostredie.</p>
@endif
<p><strong>Vložte email a budeme vám automaticky zasielať notifikácie o výstavbe a zmenách vo vašom okolí:</strong></p>
{!! form($form) !!}
</div>
<div class="col-md-2"></div>
</div>

<div class="row footer">
    <div class="col-md-2"></div>
    <div class="col-md-6 col-xs-12">
        <small>Podmienky použitia: Služba poskytuje Cyklokoalícia. Vaša emailová adresa bude použitá na zasielanie notifikácií o EIA projektoch a činnostiach, ktoré Cyklokoalícia vykonáva. Zadaním emailovej adresy súhlasíte s týmto využitím. Odkaz na odhlásenie nájdete v pätičke každého zaslaného emailu.</small><br />
        <small>Ak chcete, aby sme službu prevádzkovali aj naďalej, podporte nás sumou 10+€ na účet: SK9683300000002700175046 (variabilný symbol 0314 alebo poznámka EIA).</small>
    </div>
    <div class="col-md-2 col-xs-4 col-xs-offset-4 col-md-offset-0">
        <img src="{{ asset('images/eia-bsqr.png') }}" alt="PayBySquare podporte nás sumou 10+€" class="img-responsive" />
    </div>
    <div class="col-md-2"></div>
</div>


</body>
</html>
