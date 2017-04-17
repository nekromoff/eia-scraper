<?php

use Illuminate\Database\Seeder;

class watchoption_table_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // insert regions
        DB::table('watchoptions')->insert([ 'name' =>'Banskobystrický kraj', 'type' => 'region' ]);
        DB::table('watchoptions')->insert([ 'name' =>'Bratislavský kraj', 'type' => 'region' ]);
        DB::table('watchoptions')->insert([ 'name' =>'Košický kraj', 'type' => 'region' ]);
        DB::table('watchoptions')->insert([ 'name' =>'Nitriansky kraj', 'type' => 'region' ]);
        DB::table('watchoptions')->insert([ 'name' =>'Prešovský kraj', 'type' => 'region' ]);
        DB::table('watchoptions')->insert([ 'name' =>'Trenčiansky kraj', 'type' => 'region' ]);
        DB::table('watchoptions')->insert([ 'name' =>'Trnavský kraj', 'type' => 'region' ]);
        DB::table('watchoptions')->insert([ 'name' =>'Žilinský kraj kraj', 'type' => 'region' ]);

        // insert districts
        DB::table('watchoptions')->insert([ 'name' => 'Bánovce nad Bebravou','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Banská Bystrica','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Banská Štiavnica','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Bardejov','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Bratislava I','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Bratislava II','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Bratislava III','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Bratislava IV','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Bratislava V','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Brezno','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Bytča','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Čadca','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Detva','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Dolný Kubín','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Dunajská Streda','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Galanta','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Gelnica','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Hlohovec','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Humenné','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Ilava','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Kežmarok','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Komárno','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Košice I','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Košice II','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Košice III','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Košice IV','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Košice-okolie','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Krupina','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Kysucké Nové Mesto','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Levice','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Levoča','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Liptovský Mikuláš','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Lučenec','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Malacky','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Martin','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Medzilaborce','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Michalovce','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Myjava','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Námestovo','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Nitra','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Nové Mesto nad Váhom','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Nové Zámky','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Partizánske','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Pezinok','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Piešťany','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Poltár','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Poprad','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Považská Bystrica','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Prešov','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Prievidza','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Púchov','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Revúca','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Rimavská Sobota','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Rožňava','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Ružomberok','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Sabinov','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Senec','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Senica','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Skalica','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Snina','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Sobrance','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Spišská Nová Ves','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Stará Ľubovňa','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Stropkov','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Svidník','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Šaľa','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Topoľčany','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Trebišov','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Trenčín','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Trnava','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Turčianske Teplice','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Tvrdošín','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Veľký Krtíš','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Vranov nad Topľou','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Zlaté Moravce','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Zvolen','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Žarnovica','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Žiar nad Hronom','type' => 'district' ]);
        DB::table('watchoptions')->insert([ 'name' => 'Žilina','type' => 'district' ]);
    }
}
