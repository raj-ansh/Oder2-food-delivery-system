<?php

/**
 * File name: CategoryDataTable.php
 * Last modified: 2020.04.30 at 08:21:08
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\DataTables;

use App\Models\Businesshead;
use App\Models\Contry;
use App\Models\CustomField;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Barryvdh\DomPDF\Facade as PDF;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;

class BusinessheadDataTable extends DataTable {

    /**
     * custom fields columns
     * @var array
     */
    public static $customFields = [];
    private $roleRepository;
    private $userRepository;

    public function __construct(RoleRepository $roleRepo, UserRepository $userRepo) {

        $this->roleRepository = $roleRepo;
        $this->userRepository = $userRepo;
    }

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
                ->addColumn('action', 'businesshead.datatables_actions')
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

        $hasCustomField = in_array(Businesshead::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Businesshead::class)->where('in_table', '=', true)->get();
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
    public function query(Businesshead $businessHead, Contry $countryHead) {
        $user = $this->userRepository->findWithoutFail(auth()->id());
        $role = $this->roleRepository->pluck('name', 'name')->toArray();
        $rolesSelected = $user->getRoleNames()->toArray();
        $role = $rolesSelected[0];
        if ($role != 'admin') {
            if (in_array($role, $rolesSelected)) {
                $row = $countryHead->newQuery()->where('user_id', auth()->id())->get();
                return $businessHead->newQuery()->where('chain_id', $row[0]->id);
            }
        } else {
            return $businessHead->newQuery();
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
        return 'businessheaddatatable_' . time();
    }

    /**
     * 
     * For testing purpose
     * 
     */
    public function test() {
        $user = $this->userRepository->findWithoutFail(auth()->id());
        $role = $this->roleRepository->pluck('name', 'name')->toArray();
        $rolesSelected = $user->getRoleNames()->toArray();
        $role = $rolesSelected[0];
    }

}
