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
