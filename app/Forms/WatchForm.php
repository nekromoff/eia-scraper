<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;
use \Illuminate\Support\Facades\DB;

class WatchForm extends Form
{
    public function buildForm()
    {

        $localities=DB::table('watchoptions')->get()->pluck('name', 'id')->toArray();

        $this
            ->add('email', 'email', ['label' => 'Váš email:', 'rules' => 'required', 'error_messages' => [
                    'email.required' => 'Email je potrebné vyplniť, aby sme vám mohli zaslať automatické upozornenie na výstavbu vo vašom okolí.'
            ]])
            ->add('locality', 'select', ['label' => 'Lokalita, ktorú chcete sledovať (kraj / okres):', 'choices' =>  $localities])
            ->add('otherlocality', 'text', ['label' => 'alebo zadajte inú:', 'help_block' => [
                'text' => 'Môžete zadať vlastnú lokalitu (napr. "Bratislava" pre sledovanie všetkých častí Bratislavy alebo "Banská", ak chcete sledovať všetky obce so slovom "Banská" v názve alebo "Rosina" len pre obec Rosina atď.). Ak necháte prázdne, použijeme vybranú lokalitu vyššie.',
                'tag' => 'p',
                'attr' => ['class' => 'help-block']
            ]])
            ->add('submit', 'submit', ['label' => '<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> Sledovať', 'attr' => ['class' => 'btn btn-primary btn-lg btn-block']]);
    }
}
