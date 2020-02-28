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

<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8"><small>Podmienky použitia: Služba poskytuje Cyklokoalícia. Vaša emailová adresa bude použitá na zasielanie notifikácií o EIA projektoch a činnostiach, ktoré Cyklokoalícia vykonáva. Zadaním emailovej adresy súhlasíte s týmto využitím. Odkaz na odhlásenie nájdete v pätičke každého zaslaného emailu.</small></div>
    <div class="col-md-2"></div>
</div>

</div>

</body>
</html>
