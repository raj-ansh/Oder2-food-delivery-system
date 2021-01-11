<?php

/**
 * File name: CategoryDataTable.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\bdm;
use App\Models\Contry;
use App\Models\Businesshead;
use App\Models\RegManager;
use App\Models\Supplyhead;
use App\Models\Servicecebdm;
use App\Models\CustomField;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade as PDF;

class bdmDataTable extends DataTable {

    /**
     * custom fields columns
     * @var array
     */
    public static $customFields = [];

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query) {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
                ->editColumn('image', function ($category) {
                    return getMediaColumn($category, 'image');
                })
                ->editColumn('updated_at', function ($category) {
                    return getDateColumn($category, 'updated_at');
                })
                ->addColumn('action', 'bdm.datatables_actions')
                ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns() {
        $columns = [
            [
                'data' => 'name',
                'title' => trans('lang.category_name'),
            ],
            [
                'data' => 'gender',
                'title' => trans('Gender'),
            ],
            [
                'data' => 'email',
                'title' => trans('Email'),
            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.category_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(bdm::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', bdm::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                'data' => 'custom_fields.' . $field->name . '.view',
                'title' => trans('lang.category_' . $field->name),
                'orderable' => false,
                'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(bdm $model) {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery();
        }


        if (auth()->user()->hasRole('marketer_chd')) {
            $countryHead = new Contry();
            return $this->apply($model, $countryHead);
        }

        if (auth()->user()->hasRole('marketer_bh')) {
            $businessHead = new Businesshead();
            return $this->apply($model, $businessHead);
        }

        if (auth()->user()->hasRole('marketer_rh')) {
            $regionalHead = new RegManager();
            return $this->apply($model, $regionalHead);
        }
        if (auth()->user()->hasRole('marketer_ssh')) {
            $supplyHead = new Supplyhead();
            return $this->apply($model, $supplyHead);
        }
        if (auth()->user()->hasRole('marketer_sbdm')) {
            $serviceBdm = new Servicecebdm();
            return $this->apply($model, $serviceBdm);
        }
        
        if (auth()->user()->hasRole('marketer_bdm')) {
            return $model->newQuery();
        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html() {
        return $this->builder()
                        ->columns($this->getColumns())
                        ->minifiedAjax()
                        ->addAction(['title' => trans('lang.actions'), 'width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
                        ->parameters(array_merge(
                                        config('datatables-buttons.parameters'), [
                            'language' => json_decode(
                                    file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                                    ), true)
                                        ]
        ));
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf() {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename() {
        return 'bdmdatatable_' . time();
    }

    private function apply($model, $alias) {
        $row = $alias->newQuery()->where('user_id', auth()->id())->get();

        if (auth()->user()->hasRole('marketer_chd')) {
            return $model->newQuery()->join('servicecebdm', 'servicecebdm.id', '=', 'bdm.chain_id')
                            ->join('supplyhead', 'supplyhead.id', '=', 'servicecebdm.chain_id')
                            ->join('regionalmanager', 'regionalmanager.id', '=', 'supplyhead.chain_id')
                            ->join('businesshead', 'businesshead.id', '=', 'regionalmanager.chain_id')
                            ->join('country_head', 'country_head.id', '=', 'businesshead.chain_id')
                            ->where('country_head.id', $row[0]->id)
                            ->select('bdm.name as name'
                                    , 'bdm.gender as gender', 'bdm.email as email',
                                    'bdm.updated_at as updated_at'
            );
        }

        if (auth()->user()->hasRole('marketer_bh')) {
            $row = $alias->newQuery()->where('user_id', auth()->id())->get();
            return $model->newQuery()->join('servicecebdm', 'servicecebdm.id', '=', 'bdm.chain_id')
                            ->join('supplyhead', 'supplyhead.id', '=', 'servicecebdm.chain_id')
                            ->join('regionalmanager', 'regionalmanager.id', '=', 'supplyhead.chain_id')
                            ->join('businesshead', 'businesshead.id', '=', 'regionalmanager.chain_id')
                            ->where('businesshead.id', $row[0]->id)
                            ->select('bdm.name as name'
                                    , 'bdm.gender as gender', 'bdm.email as email',
                                    'bdm.updated_at as updated_at'
            );
        }

        if (auth()->user()->hasRole('marketer_rh')) {
            $row = $alias->newQuery()->where('user_id', auth()->id())->get();
            return $model->newQuery()->join('servicecebdm', 'servicecebdm.id', '=', 'bdm.chain_id')
                            ->join('supplyhead', 'supplyhead.id', '=', 'servicecebdm.chain_id')
                            ->join('regionalmanager', 'regionalmanager.id', '=', 'supplyhead.chain_id')
                            ->where('regionalmanager.id', $row[0]->id)
                            ->select('bdm.name as name'
                                    , 'bdm.gender as gender', 'bdm.email as email',
                                    'bdm.updated_at as updated_at'
            );
        }

        if (auth()->user()->hasRole('marketer_ssh')) {
            $row = $alias->newQuery()->where('user_id', auth()->id())->get();
            return $model->newQuery()->join('servicecebdm', 'servicecebdm.id', '=', 'bdm.chain_id')
                            ->join('supplyhead', 'supplyhead.id', '=', 'servicecebdm.chain_id')
                            ->where('supplyhead.id', $row[0]->id)
                            ->select('bdm.name as name'
                                    , 'bdm.gender as gender', 'bdm.email as email',
                                    'bdm.updated_at as updated_at'
            );
        }

        if (auth()->user()->hasRole('marketer_sbdm')) {
            $row = $alias->newQuery()->where('user_id', auth()->id())->get();
            return $model->newQuery()->join('servicecebdm', 'servicecebdm.id', '=', 'bdm.chain_id')
                            ->where('servicecebdm.id', $row[0]->id)
                            ->select('bdm.name as name'
                                    , 'bdm.gender as gender', 'bdm.email as email',
                                    'bdm.updated_at as updated_at'
            );
        }
    }

}
