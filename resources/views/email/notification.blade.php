@include('email.header')
            <!-- START CENTERED WHITE CONTAINER -->
            <span class="preheader"><strong>Nové EIA: {{ $project->name }}</strong><br /><br />
            Okres:
            @foreach ($project->districts as $district)
                {{ $district->name }}
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
            <br />
            Obec:
            @foreach ($project->localities as $locality)
                {{ $locality->name }}
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
            Stav: <strong>{{ $project->status }}</strong><br />
            Typ: {{ $project->type }}<br /><br />
            {{ $project->description }}<br /><br />
            URL: {{ $project->url }}<br /><br/>
            <strong>Súvisiace dokumenty:</strong>
            @foreach ($project->documents as $document)
                {{ $document->name }}: {{ $document->url }}<br />
            @endforeach
            </span>
            <table class="main">

              <!-- START MAIN CONTENT AREA -->
              <tr>
                <td class="wrapper">
                  <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
                        <h1>EIA: {{ $project->name }}</h1>

                        <p>
                        Okres:
                        @foreach ($project->districts as $district)
                            {{ $district->name }}
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                        <br />
                        Obec:
                        @foreach ($project->localities as $locality)
                            {{ $locality->name }}
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                        <br />
                        Stav: <strong>{{ $project->status }}</strong><br />
                        Typ: {{ $project->type }}<br /><br />
                        {{ $project->description }}
                        </p>
                        <p><a href="{{ $project->url }}">{{ $project->url }}</a></p>
                        <p><strong>Súvisiace dokumenty:</strong></p>
                        <ul>
                        @foreach ($project->documents as $document)
                            <li><a href="http://www.enviroportal.sk{{ $document->url }}">{{ $document->name }}</a>
                            @if ($document->mimefiletype=='application/pdf')
                                (PDF)
                            @elseif ($document->mimefiletype=='application/rtf')
                                (RTF)
                            @elseif ($document->mimefiletype=='application/msword')
                                (DOC)
                            @elseif ($document->mimefiletype=='application/msexcel')
                                (XLS)
                            @elseif ($document->mimefiletype=='images/jpeg')
                                (JPG)
                            @elseif ($document->mimefiletype=='application/zip')
                                (ZIP)
                            @endif
                            </li>
                        @endforeach
                        </ul>
@include('email.footer')