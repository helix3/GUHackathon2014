<?php

class HomeController extends HackController
{
    /**
     * sas
     *
     * @var \Hack\Repositories\Sas\SasInterface
     */
    private $sas;

    /**
     * @param \Hack\Repositories\Sas\SasInterface $sas
     */
    function __construct(\Hack\Repositories\Sas\SasInterface $sas)
    {

        $this->sas = $sas;

        $this->search = new \Elasticsearch\Client(Config::get('elasticsearch.settings'));

    }

    public function indexNew()
    {

        $limit = 10;
        $page = Input::get('page', 1);


        $data = json_decode(json_encode($this->search->search([
            'index' => 'sas',
            'body' => [
                'query' => [
                    "multi_match" => [
                        "query" => Input::get('search', ' a '),
                        "type" => "best_fields",
                        "fields" => ["_all"]
                    ]
                ],

                'size' => $limit,
                'from' => $limit * ($page - 1),
            ]
        ])));

        if (\Request::ajax()) {

            return Response::json(
                $data->hits->hits
            );

        }


        $this->render('hack::index', [
            'data' => $data->hits->hits,

        ]);
    }

    // Old deprecated method to search without ElasticSearch
    public function index()
    {

        $data = $this->sas->make([])
            ->Where(function ($query) {
                $query->where('country', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('city', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('date', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('cost', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('notes', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('weapons', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('motive', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('target_type', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('attack_type', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('date', 'LIKE', '%' . Input::get('date') . '%')
                    ->orWhere('body', 'LIKE', '%' . Input::get('search') . '%')
                    ->orWhere('notes', 'LIKE', '%' . Input::get('search') . '%');
            })
            ->Where(function ($query) {
                $date = explode(" ", Input::get('search'));

                if (array_key_exists('1', $date)) {
                    $query->where('date', 'LIKE', '%' . $date[1] . '%');
                }

            })
            ->orderBy('weapons')
            ->paginate(15);

        if (\Request::ajax()) {

            return Response::json(
                $data->toArray()
            );

        }


        $this->render('hack::index', [
            'data' => $data,

        ]);
    }

    /**
     * Redirect from FORM filter to URL
     *
     * @return mixed
     */
    public function filter()
    {
        // Strip out previous input from previous url.
        preg_match("/.*?((?:\\/[\\w\\.\\-]+)+)/is", URL::previous(), $url);

        return Redirect::to($url[0] . '?' . http_build_query(Input::except('_token')));
    }


    public function stats()
    {

        $data = ''; //$this->sas->find($data_id);

        $this->render('hack::stats', [
            'data' => $data,

        ]);
    }

    public function about()
    {

        $data = ''; //$this->sas->find($data_id);

        $this->render('hack::about', [
            'data' => $data,

        ]);
    }

}
