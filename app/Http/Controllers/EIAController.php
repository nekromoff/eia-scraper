<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mail;
use Guzzle;
use Goutte;
use Kris\LaravelFormBuilder\FormBuilder;
use App\Mail\ProjectNotification;
use App\Forms\WatchForm;
use App\Watcher;
use App\Watchoption;
use App\Project;
use App\Document;

class EIAController extends Controller
{
    public function index(FormBuilder $formBuilder) {
        $form = $formBuilder->create(WatchForm::class, [
            'method' => 'POST',
            'url' => route('store')
        ]);

        return view('index', compact('form'));
    }

    public function about() {
        return view('about');
    }

    public function get() {
        $this->retrieveData();

    }

    public function updateFiles() {
        $this->updateContentTypes();
    }

    function unsubscribe(Request $request)
    {
        $all=false;
        $existingwatcher=\App\Watcher::where('id', $request->watcherid)->where('email', $request->email)->first();
        if (!isset($existingwatcher->id)) return redirect()->route('index')->with('error', 'Nevieme vás odhlásiť, lebo email '.$request->email.' sme nenašli. Asi ste sa už odhlásili v minulosti.');
        $hash=sha1($existingwatcher->id.$existingwatcher->search.$existingwatcher->created_at);
        if ($request->hash!=$hash) return redirect()->route('index')->with('error', 'Nevieme vás odhlásiť, lebo odkaz na odhlásenie nie je funkčný. Skúste ho skopírovať ešte raz v celej dĺžke alebo kliknúť na odhlásenie priamo z emailu.');
        \App\Watcher::destroy($request->watcherid);
        return redirect()->route('index')->with('message', 'Úspešne sme odhlásili váš email '.$request->email.' z EIA notifikácií pre lokalitu: '.$existingwatcher->search.'.');
    }

    public function storeForm(FormBuilder $formBuilder, Request $request)
    {

        $form = $formBuilder->create(\App\Forms\WatchForm::class);

        if (!$form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        $watcher = new Watcher;
        $watcher->email = strtolower($request->email);
        if (isset($request->otherlocality)) $watcher->search = $request->otherlocality;
        else {
            $locality=\App\Watchoption::find($request->locality);
            $locality->name=str_ireplace(' kraj','',$locality->name);
            $watcher->search=$locality->name;
        }

        $watcher->search=trim(ucwords($watcher->search));

        $existingwatcher=\App\Watcher::where('email', $watcher->email)->where('search', $watcher->search)->first();

        $notifycount=0;
        // create new watcher only if it already does not exist
        if (!isset($existingwatcher)) {
            $watcher->save();

            $projects=\App\Project::with('regions')->with('districts')->with('localities')->with('companies.company')->with('documents')->where('updated_at', '>=', \Carbon\Carbon::now()->subDays(7))->get();
            foreach ($projects as $project) {
                $notify=0;
                $project->url=str_replace('/print','',$project->url); // remove /print from URL
                $project->url=str_replace('.sk/eia/','.sk/sk/eia/',$project->url); // add /sk/ to URL
                foreach($project->regions->pluck('name')->toArray() as $region) {
                    if (stripos($region,$watcher->search)!==FALSE) $notify=1;
                }
                foreach($project->districts->pluck('name')->toArray() as $district) {
                    if (stripos($district,$watcher->search)!==FALSE) $notify=1;
                }
                foreach($project->localities->pluck('name')->toArray() as $locality) {
                    if (stripos($locality,$watcher->search)!==FALSE) $notify=1;
                }
                if ($notify) {
                    $hash=sha1($watcher->id.$watcher->search.$watcher->created_at);
                    $project->setAttribute('unsubscribelinkloc',route('unsubscribe', [$watcher->email, $hash, $watcher->id]));
                    Mail::to($watcher->email)->send(new ProjectNotification($project));
                    Log::info('Notifying '.$watcher->email.': '.$project->name);
                    $notifycount++;
                }
            }
        }

        $message='Budeme vám zasielať upozornenia na '.$watcher->email.' pre lokalitu: '.$watcher->search.'.';
        if ($notifycount>0) {
            $message.=' Tiež sme vám poslali upozornenia na '.$notifycount;
            if ($notifycount==1) $message.=' projekt';
            if ($notifycount>=2 AND $notifycount<=4) $message.=' projekty';
            if ($notifycount>=5) $message.=' projektov';
            $message.=' EIA, ktoré boli pridané za posledných 7 dní a vyhovujú vašej požiadavke.';
        }

        return redirect()->route('index')->with('message', $message);

    }

    public function sendNotifications($project_id) {
        $project=\App\Project::with('regions')->with('districts')->with('localities')->with('companies.company')->with('documents')->find($project_id);
        $watchers=\App\Watcher::get();
        $notified_emails=[];
        $project->url=str_replace('/print','',$project->url); // remove /print from URL
        $project->url=str_replace('.sk/eia/','.sk/sk/eia/',$project->url); // add /sk/ to URL
        foreach ($watchers as $watcher) {
            $notify=0;
            foreach($project->regions->pluck('name')->toArray() as $region) {
                if (stripos($region,$watcher->search)!==FALSE) $notify=1;
            }
            foreach($project->districts->pluck('name')->toArray() as $district) {
                if (stripos($district,$watcher->search)!==FALSE) $notify=1;
            }
            foreach($project->localities->pluck('name')->toArray() as $locality) {
                if (stripos($locality,$watcher->search)!==FALSE) $notify=1;
            }
            if ($notify and in_array($watcher->email,$notified_emails)===FALSE) {
                Mail::to($watcher->email)->send(new ProjectNotification($project));
                Log::info('Notifying '.$watcher->email.': '.$project->name);
                $notified_emails[]=$watcher->email;
            }
        }
    }

    public function debugProject(Request $request) {
        $project=\App\Project::with('regions')->with('districts')->with('localities')->with('companies.company')->with('institutions.institution')->with('stakeholders.stakeholder')->with('documents')->find($request->id);
        return dd($project);
    }
/*
    public function refreshFiles() {
        $crawler=Goutte::request('GET', 'http://www.enviroportal.sk/sk/eia/print');
        $crawler->filter('tr:not(.head)')->each(function ($line) use (&$i, &$found) {
            $url='http://www.enviroportal.sk'.str_replace('/sk/','/',$line->filter('a')->attr('href')).'/print';
            $detail=Goutte::request('GET', $url);
            $o=0;
            $detail->filter('a')->each(function ($node) use (&$i, &$found, &$o) {
                if (strpos($node->attr('href'), 'eia/dokument')!==FALSE) {
                    $found[$i]['doc'][$o]['name']=trim($node->text());
                    $found[$i]['doc'][$o]['url']=trim($node->attr('href'));
                    $o++;
                }
            });
        }
    }
*/
    public function retrieveData($searchparams='') {
        // search[country]=1
        // $parameters='search[country]=1';
        $crawler=Goutte::request('GET', 'http://www.enviroportal.sk/sk/eia/print');
        $fingerprint=sha1($crawler->html());
        $systemstate=\App\Systemstate::where('key','fingerprint')->first();
        // if matching fingerprint exists, skip further data retrieval
        if (isset($systemstate->value) AND $fingerprint==$systemstate->value) {
            echo 'fingerprint match; skipping';
            return;
        }
        else { // update fingerprint
            $systemstate=\App\Systemstate::updateOrCreate(['key' => 'fingerprint'], ['value' => $fingerprint]);
        }
        $i=0; $found=array();
        $crawler->filter('tr:not(.head)')->each(function ($line) use (&$i, &$found) {
            $cells=$line->children()->extract('_text');
            $url='http://www.enviroportal.sk'.str_replace('/sk/','/',$line->filter('a')->attr('href')).'/print';
            foreach ($cells as $key=>$cell) {
                $cells[$key]=trim($cell);
            }
            $baseinfo=$cells[0];
            $region=$cells[1];
            $district=$cells[2];
            $industry=$cells[3];
            $parts=explode("\n",$baseinfo);
            $state=trim(str_replace('Stav: ','',$parts[2]));
            $detail=Goutte::request('GET', $url);
            $found[$i]['name']=trim($detail->filter('h2')->first()->text());
            $found[$i]['url']=$url;
            $found[$i]['status']=trim($state);
            if (isset($region)) {
                $parts=explode("\n",trim($region));
                foreach ($parts as $part) {
                    if (trim($part)) $found[$i]['region'][]=trim($part);
                }
            }
            if (isset($district)) {
                $parts=explode("\n",trim($district));
                foreach ($parts as $part) {
                    if (trim($part)) $found[$i]['district'][]=trim($part);
                }
            }
            $found[$i]['type']=$industry;
            $detail->filter('.table-list')->each(function ($node) use (&$i, &$found) {
                $text=$node->text();
                $text=str_replace("\t",'',$text);
                $text=str_replace("\n\n","\n",$text);
                //dump($text);
                preg_match('/Zákon:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['act']=trim($matches[1]);
                preg_match('/Činnosť:(.+)/s', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['activity']=trim($matches[1]);
                preg_match('/Oblasť:(.+)/s', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['activity']=trim($matches[1]);
                preg_match('/ESPOO dohovor:(.+)/s', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['espoo']=trim($matches[1]);
                preg_match('/Účel akcie:(.+)Dotknutá/s', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['description']=trim($matches[1]);
                preg_match('/Dotknutá obec:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) {
                    // ignore district info that is part of locality
                    if (strpos($matches[1],"(okres")!==FALSE) $matches[1]=substr($matches[1],0,strpos($matches[1],"(okres"));
                    if (strpos($matches[1],"\n")!==FALSE) $parts=explode("\n",trim($matches[1]));
                    else $parts=explode(",",trim($matches[1]));
                    foreach ($parts as $part) {
                        if (trim($part)) $found[$i]['locality'][]=trim($part);
                    }
                }
                preg_match('/Príslušný orgán:(.+)Navrhovateľ:/s', $node->text(), $matches);
                if (isset($matches[1])) {
                    $parts=explode("\n",trim($matches[1]));
                    foreach ($parts as $part) {
                        if (trim($part)) $found[$i]['institution']['primary'][]=trim($part);
                    }
                }
                preg_match('/Navrhovateľ:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['company']['name']=trim($matches[1]);
                preg_match('/IČO Navrhovateľa:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['company']['ico']=trim($matches[1]);
                preg_match('/Príslušný orgán:(.+)Obstarávateľ:/s', $node->text(), $matches);
                if (isset($matches[1])) {
                    $parts=explode("\n",trim($matches[1]));
                    foreach ($parts as $part) {
                        if (trim($part)) $found[$i]['institution']['primary'][]=trim($part);
                    }
                }
                preg_match('/Obstarávateľ:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['company']['name']=trim($matches[1]);
                preg_match('/IČO Obstarávateľa:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['company']['ico']=trim($matches[1]);
                preg_match('/Povoľujúci orgán:(.*)Dokumenty/s', $node->text(), $matches);
                if (isset($matches[1])) {
                    $parts=explode("\n",trim($matches[1]));
                    foreach ($parts as $part) {
                        if (trim($part)) $found[$i]['institution']['secondary'][]=trim($part);
                    }
                }
                preg_match('/Schvaľujúci orgán:(.*)Dokumenty/s', $node->text(), $matches);
                if (isset($matches[1])) {
                    $parts=explode("\n",trim($matches[1]));
                    foreach ($parts as $part) {
                        if (trim($part)) $found[$i]['institution']['secondary'][]=trim($part);
                    }
                }
                preg_match('/Strana pôvodu(.+)/', $node->text(), $matches);
                if (isset($matches[1])) {
                    $parts=explode("\n",trim($matches[1]));
                    foreach ($parts as $part) {
                        if (trim($part)) $found[$i]['stakeholder']['primary'][]=trim($part);
                    }
                }
                preg_match('/Dotknutá strana(.+)/', $node->text(), $matches);
                if (isset($matches[1])) {
                    $parts=explode("\n",trim($matches[1]));
                    foreach ($parts as $part) {
                        if (trim($part)) $found[$i]['stakeholder']['secondary'][]=trim($part);
                    }
                }
                preg_match('/Dátum zverejnenia zámeru(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['date']['proposal']=trim($matches[1]);
                preg_match('/Dátum zverejnenia podnetu(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['date']['proposal']=trim($matches[1]);
                preg_match('/Spracovateľ zámeru:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['creator']=trim($matches[1]);
                preg_match('/Dátum zverejnenia zmeny(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['date']['change']=trim($matches[1]);
                preg_match('/Dátum zverejnenia správy o hodnotení:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['date']['report']=trim($matches[1]);
                preg_match('/Dôvod ukončenia posudzovania:(.+)/', $node->text(), $matches);
                if (isset($matches[1])) $found[$i]['endreason']=trim($matches[1]);

                // faza 1
                // Dátum zverejnenia zámeru
                // Text zámeru:
                // Oznámenie o predložení zámeru:
                // Spracovateľ zámeru:
                // ----- optional - -- -
                // Upustenie od spracovania správy o hodnotení:
                // Verejné prerokovanie:

                // faza 1
                // Text oznámenia:
                // Informácia o oznámení:
                // Text rozhodnutia zo zisťovacieho konania:

                // faza 1
                // Dátum zverejnenia podnetu
                // Podnet podal
                // Text podnetu:
                // Rozhodnutie o podnete:

                // faza 2 (viacero?)
                // Dátum zverejnenia zmeny
                // Text oznámenia o zmene:
                // Rozhodnutie o zmene činnosti.:
                // Vyjadrenie:

                // faza 2
                // Text rozsahu hodnotenia strategického dokumentu:
                // Text strategického dokumentu:

                // faza 3
                // Dátum zverejenenia správy o hodnotení
                // Text správy o hodnotení:
                // Spracovateľ správy o hodnotení:
                // Informácia o správe o hodnotení:
                // Verejné prerokovanie:


                // faza 3
                // Dôvod ukončenia posudzovania:

                // faza 3
                // Text záverečného stanoviska:
            });
            $o=0;
            $detail->filter('a')->each(function ($node) use (&$i, &$found, &$o) {
                if (strpos($node->attr('href'), 'eia/dokument')!==FALSE) {
                    $found[$i]['doc'][$o]['name']=trim($node->text());
                    $found[$i]['doc'][$o]['url']=trim($node->attr('href'));
                    $o++;
                }
            });
            // create hash from serialized string containing URL, status and existing docs
            $found[$i]['hash']=sha1(serialize($found[$i]['url']).serialize($found[$i]['status']).serialize($found[$i]['doc']));
            // TODO: keep updated projects in queue
            $project=\App\Project::where('hash',$found[$i]['hash'])->first();
            // if project already exists, remove it from queue
            if (isset($project->id)) {
                unset($found[$i]);
            }
            $i++;
        });

        //print_r($found); exit;

        // process retrieved data
        foreach ($found as $item) {
            $project=\App\Project::where('url',$item['url'])->first();

            // create new project, if it does not exists
            if (!isset($project->id)) {

                $project=new \App\Project;
                $project->name=$item['name'];
                $project->url=$item['url'];
                $project->type=$item['type'];
                $project->status=$item['status'];
                $project->act=$item['act'];
                $project->activity=$item['activity'];
                $project->espoo=!isset($item['espoo']) ? '' : $item['espoo'];
                $project->description=!isset($item['description']) ? '' : $item['description'];
                $project->hash=$item['hash'];
                $project->save();

                $company=\App\Company::where('ico',$item['company']['ico'])->first();
                if (isset($company->id)) {
                    $projectcompany=new \App\ProjectsCompany;
                    $projectcompany->project_id=$project->id;
                    $projectcompany->company_id=$company->id;
                    $projectcompany->save();
                }
                else {
                    $company=new \App\Company;
                    $company->name=$item['company']['name'];
                    $company->ico=$item['company']['ico'];
                    $company->save();
                    $projectcompany=new \App\ProjectsCompany;
                    $projectcompany->project_id=$project->id;
                    $projectcompany->company_id=$company->id;
                    $projectcompany->save();
                }

                if (isset($items['doc'])) {
                    foreach ($item['doc'] as $documentdetails) {
                        $document=new \App\Document;
                        $document->project_id=$project->id;
                        $document->name=$documentdetails['name'];
                        $document->url=$documentdetails['url'];
                        $document->mimefiletype='_unknown';
                        $document->save();
                    }
                }

                foreach ($item['region'] as $regionname) {
                    $region=new \App\ProjectsRegion;
                    $region->project_id=$project->id;
                    $region->name=$regionname;
                    $region->save();
                }

                foreach ($item['district'] as $districtname) {
                    $district=new \App\ProjectsDistrict;
                    $district->project_id=$project->id;
                    $district->name=$districtname;
                    $district->save();
                }

                foreach ($item['locality'] as $localityname) {
                    $locality=new \App\ProjectsLocality;
                    $locality->project_id=$project->id;
                    $locality->name=$localityname;
                    $locality->save();
                }

                /** based on: http://www.enviroportal.sk/eia/detail/uzemny-plan-zony-technologicky-park-cepit-bratislava-vajnory/print
                 *  not all EIA projects have to have primary institution, which is weird, but...
                 *  therefore isset condition added
                 *  2017-04-24
                 */
                if (isset($item['institution']['primary'])) {
                    foreach ($item['institution']['primary'] as $institutionname) {
                        $institution=\App\Institution::where('name',$institutionname)->first();
                        if (isset($institution->id)) {
                            $projectinstitution=new \App\ProjectsInstitution;
                            $projectinstitution->project_id=$project->id;
                            $projectinstitution->institution_id=$institution->id;
                            $projectinstitution->type='primary';
                            $projectinstitution->save();
                        }
                        else {
                            $institution=new \App\Institution;
                            $institution->name=$institutionname;
                            $institution->save();
                            $projectinstitution=new \App\ProjectsInstitution;
                            $projectinstitution->project_id=$project->id;
                            $projectinstitution->institution_id=$institution->id;
                            $projectinstitution->type='primary';
                            $projectinstitution->save();
                        }
                    }
                }
                if (isset($item['institution']['secondary'])) {
                    foreach ($item['institution']['secondary'] as $institutionname) {
                        $institution=\App\Institution::where('name',$institutionname)->first();
                        if (isset($institution->id)) {
                            $projectinstitution=new \App\ProjectsInstitution;
                            $projectinstitution->project_id=$project->id;
                            $projectinstitution->institution_id=$institution->id;
                            $projectinstitution->type='secondary';
                            $projectinstitution->save();
                        }
                        else {
                            $institution=new \App\Institution;
                            $institution->name=$institutionname;
                            $institution->save();
                            $projectinstitution=new \App\ProjectsInstitution;
                            $projectinstitution->project_id=$project->id;
                            $projectinstitution->institution_id=$institution->id;
                            $projectinstitution->type='secondary';
                            $projectinstitution->save();
                        }
                    }
                }

                if (isset($item['stakeholder']['primary'])) {
                    foreach ($item['stakeholder']['primary'] as $stakeholdername) {
                        $stakeholder=\App\Stakeholder::where('name',$stakeholdername)->first();
                        if (isset($stakeholder->id)) {
                            $projectstakeholder=new \App\ProjectsStakeholder;
                            $projectstakeholder->project_id=$project->id;
                            $projectstakeholder->stakeholder_id=$stakeholder->id;
                            $projectstakeholder->type='primary';
                            $projectstakeholder->save();
                        }
                        else {
                            $stakeholder=new \App\Stakeholder;
                            $stakeholder->name=$stakeholdername;
                            $stakeholder->save();
                            $projectstakeholder=new \App\ProjectsStakeholder;
                            $projectstakeholder->project_id=$project->id;
                            $projectstakeholder->stakeholder_id=$stakeholder->id;
                            $projectstakeholder->type='primary';
                            $projectstakeholder->save();
                        }
                    }
                }
                if (isset($item['stakeholder']['secondary'])) {
                    foreach ($item['stakeholder']['secondary'] as $stakeholdername) {
                        $stakeholder=\App\Stakeholder::where('name',$stakeholdername)->first();
                        if (isset($stakeholder->id)) {
                            $projectstakeholder=new \App\ProjectsStakeholder;
                            $projectstakeholder->project_id=$project->id;
                            $projectstakeholder->stakeholder_id=$stakeholder->id;
                            $projectstakeholder->type='secondary';
                            $projectstakeholder->save();
                        }
                        else {
                            $stakeholder=new \App\Stakeholder;
                            $stakeholder->name=$stakeholdername;
                            $stakeholder->save();
                            $projectstakeholder=new \App\ProjectsStakeholder;
                            $projectstakeholder->project_id=$project->id;
                            $projectstakeholder->stakeholder_id=$stakeholder->id;
                            $projectstakeholder->type='secondary';
                            $projectstakeholder->save();
                        }
                    }
                }
            }
            else {
                // update existing (changed) project

                $project->status=$item['status'];
                $project->hash=$item['hash'];
                $project->save();

                if (isset($item['doc'])) {
                    foreach ($item['doc'] as $documentdetails) {
                        $document=\App\Document::where('url',$documentdetails['url'])->first();
                        // create document, if it does not exist
                        if (!isset($document->id)) {
                            $document=new \App\Document;
                            $document->project_id=$project->id;
                            $document->name=$documentdetails['name'];
                            $document->url=$documentdetails['url'];
                            $document->mimefiletype='_unknown';
                            $document->save();
                        }
                    }
                }

            }

        $this->sendNotifications($project->id);

        }
    }

    private function updateContentTypes() {
        $documents=\App\Document::where('mimefiletype','_unknown')->get();
        foreach ($documents as $document) {
            //echo $document->url,'<br />'; flush(); ob_flush();
            $response=Guzzle::head('http://www.enviroportal.sk'.$document->url);
            $contenttype=$response->getHeader('content-type');
            // fix broken reporting by server - ZIP is served as HTML:
            if ($contenttype[0]=='text/html') $contenttype[0]='application/zip';
            $document->mimefiletype=$contenttype[0];
            $document->save();
        }
    }

}