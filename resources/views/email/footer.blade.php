@section('footer')


            <!-- START FOOTER -->
            <div class="footer">
              <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="content-block powered-by">
                    <small>Službu poskytuje Cyklokoalícia. <strong>Ak chcete, aby sme službu prevádzkovali aj naďalej, podporte nás sumou 10+€ na účet: SK9683300000002700175046 (variabilný symbol 0314 alebo poznámka EIA).</strong></small><br/>
                    <img src="{{ $message->embed(public_path().'/images/eia-bsqr.png') }}" alt="PayBySquare" class="byqr" />
                  </td>
                </tr>
              <tr>
                  <td class="content-block">
                      <p><strong>Tento email dostávate, lebo ste sa prihlásili na odber upozornení na webe <a href="https://eia.cyklokoalicia.sk">Sleduj EIA</a>.</strong></p>
                      <p><a href="{{ $project->unsubscribelinkloc }}?all=true" title="Zrušenie odberu upozornení pre všetky lokality">Odhlásenie z odberu upozornení pre všetky lokality</a>.</p>
                  </td>
              </tr>
              </table>
            </div>

            <!-- END FOOTER -->

<!-- END CENTERED WHITE CONTAINER --></div>
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </body>
</html>
@show
