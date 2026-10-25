<?php

namespace Webkul\Alteration\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ClaimDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $prefix = DB::getTablePrefix();

        $queryBuilder = DB::table('alteration_claims')
            ->leftJoin('orders', 'alteration_claims.order_id', '=', 'orders.id')
            ->leftJoin('alteration_partners', 'alteration_claims.alteration_partner_id', '=', 'alteration_partners.id')
            ->select(
                'alteration_claims.id',
                'alteration_claims.order_id',
                'orders.increment_id',
                'alteration_claims.reason',
                'alteration_claims.status',
                'alteration_claims.fault',
                'alteration_partners.name as partner_name',
                'alteration_claims.actual_cost',
                'alteration_claims.payout_status',
                'alteration_claims.created_at',
                DB::raw('CONCAT('.$prefix.'orders.customer_first_name, " ", '.$prefix.'orders.customer_last_name) as customer_name')
            );

        $this->addFilter('id', 'alteration_claims.id');
        $this->addFilter('increment_id', 'orders.increment_id');
        $this->addFilter('customer_name', DB::raw('CONCAT('.$prefix.'orders.customer_first_name, " ", '.$prefix.'orders.customer_last_name)'));
        $this->addFilter('reason', 'alteration_claims.reason');
        $this->addFilter('status', 'alteration_claims.status');
        $this->addFilter('fault', 'alteration_claims.fault');
        $this->addFilter('partner_name', 'alteration_partners.name');
        $this->addFilter('payout_status', 'alteration_claims.payout_status');
        $this->addFilter('created_at', 'alteration_claims.created_at');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('alteration::app.admin.claims.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'increment_id',
            'label'      => trans('alteration::app.admin.claims.datagrid.order'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($row) => '#'.$row->increment_id,
        ]);

        $this->addColumn([
            'index'      => 'customer_name',
            'label'      => trans('alteration::app.admin.claims.datagrid.customer'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'reason',
            'label'              => trans('alteration::app.admin.claims.datagrid.reason'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->options('claim_reasons', 'reasons'),
            'sortable'           => true,
            'closure'            => fn ($row) => trans('alteration::app.admin.claims.reasons.'.$row->reason),
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('alteration::app.admin.claims.datagrid.status'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->options('claim_statuses', 'statuses'),
            'sortable'           => true,
            'closure'            => function ($row) {
                $class = match ($row->status) {
                    'submitted', 'info_requested'                      => 'label-pending',
                    'under_review', 'aso_fault_approved', 'partner_assigned' => 'label-processing',
                    'completed'                                        => 'label-active',
                    'customer_fault', 'rejected'                       => 'label-canceled',
                    default                                            => 'label-closed',
                };

                return '<p class="'.$class.'">'.trans('alteration::app.admin.claims.statuses.'.$row->status).'</p>';
            },
        ]);

        $this->addColumn([
            'index'              => 'fault',
            'label'              => trans('alteration::app.admin.claims.datagrid.fault'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                ['label' => trans('alteration::app.admin.claims.faults.aso'), 'value' => 'aso'],
                ['label' => trans('alteration::app.admin.claims.faults.customer'), 'value' => 'customer'],
            ],
            'sortable'           => true,
            'closure'            => fn ($row) => $row->fault
                ? trans('alteration::app.admin.claims.faults.'.$row->fault)
                : trans('alteration::app.admin.claims.none'),
        ]);

        $this->addColumn([
            'index'      => 'partner_name',
            'label'      => trans('alteration::app.admin.claims.datagrid.partner'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($row) => $row->partner_name ?: trans('alteration::app.admin.claims.none'),
        ]);

        $this->addColumn([
            'index'    => 'actual_cost',
            'label'    => trans('alteration::app.admin.claims.datagrid.cost'),
            'type'     => 'float',
            'sortable' => true,
            'closure'  => fn ($row) => $row->actual_cost !== null
                ? core()->formatBasePrice((float) $row->actual_cost)
                : trans('alteration::app.admin.claims.none'),
        ]);

        $this->addColumn([
            'index'              => 'payout_status',
            'label'              => trans('alteration::app.admin.claims.datagrid.payout'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => collect(['not_applicable', 'pending', 'paid'])
                ->map(fn ($value) => ['label' => trans('alteration::app.admin.claims.payout-statuses.'.$value), 'value' => $value])
                ->all(),
            'sortable'           => true,
            'closure'            => fn ($row) => trans('alteration::app.admin.claims.payout-statuses.'.$row->payout_status),
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('alteration::app.admin.claims.datagrid.submitted'),
            'type'            => 'date',
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'icon'   => 'icon-view',
            'title'  => trans('alteration::app.admin.claims.datagrid.view'),
            'method' => 'GET',
            'url'    => fn ($row) => route('admin.alterations.claims.view', $row->id),
        ]);
    }

    protected function options(string $configKey, string $langKey): array
    {
        return collect(config('alteration.'.$configKey))
            ->map(fn ($value) => [
                'label' => trans('alteration::app.admin.claims.'.$langKey.'.'.$value),
                'value' => $value,
            ])
            ->all();
    }
}
