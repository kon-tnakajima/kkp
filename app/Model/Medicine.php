<?php

namespace App\Model;

use App\Model\Concerns\Calc as CalcTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Medicine extends BaseModel {
    use SoftDeletes;
    use CalcTrait;

    const DOSAGE_TYPE_INTERNAL = 1;

    const DOSAGE_TYPE_EXTERNAL = 2;

    const DOSAGE_TYPE_INJECTION = 3;

    const DOSAGE_TYPE_DENTISTRY = 4;

    const DOSAGE_TYPE_OTHER = 5;

    const OWNER_CLASSIFICATION_BUNKAREN = 1;

    const OWNER_CLASSIFICATION_HQ = 2;

    const OWNER_CLASSIFICATION_HOSPITAL = 3;

    const OWNER_CLASSIFICATION_STR = array(
        self::OWNER_CLASSIFICATION_BUNKAREN => '文化連',
        self::OWNER_CLASSIFICATION_HQ       => '本部',
        self::OWNER_CLASSIFICATION_HOSPITAL => '病院',
    );

    const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT1 = ' ';

    const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT2 = '生';

    const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT3 = '特生';

    const GENERIC_PRODUCT_DETAIL_DEVISION_NOT_APPLICABLE = 0;

    const GENERIC_PRODUCT_DETAIL_DEVISION_PATENT = 1;

    const GENERIC_PRODUCT_DETAIL_DEVISION_LONG_TERM = 2;

    const GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC = 3;

    const GENERIC_PRODUCT_DETAIL_DEVISION_ORIGINAL = 4;

    const GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC2 = 5;

    const GENERIC_PRODUCT_DETAIL_DEVISION_STR = array(
        self::GENERIC_PRODUCT_DETAIL_DEVISION_NOT_APPLICABLE => '',
        self::GENERIC_PRODUCT_DETAIL_DEVISION_PATENT         => '特許品',
        self::GENERIC_PRODUCT_DETAIL_DEVISION_LONG_TERM      => '長期品',
        self::GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC        => '後発品',
        self::GENERIC_PRODUCT_DETAIL_DEVISION_ORIGINAL       => '☆先発',
        self::GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC2       => '★後発',
    );

    const DISCONTINUING_DIVISION_NONE = 0;

    const DISCONTINUING_DIVISION_STOP = 1;

    const DISCONTINUING_DIVISION_MODIFER = 2;

    protected $guraded = array('id');

    protected $fillable = array(
        'kon_medicine_id',
        'bunkaren_code',
        'code',
        'medicine_code',
        'receipt_computerized_processing1',
        'receipt_computerized_processing2',
        'popular_name',
        'name',
        'phonetic',
        'standard_unit',
        'package_presentation',
        'unit',
        'drug_price_equivalent',
        'maker_id',
        'selling_agency_code',
        'medicine_effet_id',
        'dosage_type_division',
        'transitional_deadline',
        'danger_poison_category1',
        'danger_poison_category2',
        'danger_poison_category3',
        'danger_poison_category4',
        'danger_poison_category5',
        'prescription_drug_category',
        'biological_product_classification',
        'generic_product_flag',
        'production_stop_date',
        'discontinuation_date',
        'hospital_code',
        'owner_classification',
        'deleter',
        'creater',
        'updater',
    );

    /**
     * 採用一覧取得.
     *
     * @param Request  $request  リクエスト情報
     * @param Facility $facility 施設情報
     * @param int      $count    表示件数
     * @param $user ユーザ情報
     * @return
     */
    public function listWithApplyIndex(Request $request, int $count, $user) {
        $this->start_log();

        $sql = $this->listWithApplySql($request, $count, $user);

        $count_sql = ' select count(*) from (' . $sql . ') as tmp ';
        $all_count = \DB::select($count_sql);

        $count2=$all_count[0]->count;

        $sql .= ' order by ';
        // 私が担当、私が申請、申請進行中であれば、申請日降順
        /*20220203 並び順は一覧画面で選択できるようにする
        if ($request->simple_search == 'my_charge' || $request->simple_search == 'my_apply' || $request->simple_search == 'is_applying' ){
            $sql .= ' pa_application_date desc, ';
        }
        */

        //$sql .="  maker_name asc ";
        $sql .= '  hanbai_maker asc ';
        $sql .= ' ,medicine_name asc ';
        $sql .= ' ,standard_unit asc ';
        $sql .= ' ,jan_code asc ';
        $sql .= ' ,facility_id asc ';
        $sql .= ' ,trader_name ';

        if ($count == -1) {
            $per_page = $request->page_count ?? 1;
        } else {
            if (!isset($request->page_count)) {
                $offset=0;
            } else {
                if ($request->page == 0) {
                    $offset=0;
                } else {
                    $offset=($request->page - 1) * $request->page_count;
                }
            }

            $per_page = $request->page_count ?? 1;
            $sql .= ' limit ' . $per_page . ' offset ' . $offset;
        }

        $all_rec = \DB::select($sql);
        $result  = self::query()->hydrate($all_rec);

        // ページ番号が指定されていなかったら１ページ目
        $page_num = $request->page ?? 1;
        // ページ番号に従い、表示するレコードを切り出す
        // $disp_rec = array_slice($result->all(), ($page_num - 1) * $per_page, $per_page);

        // ページャーオブジェクトを生成
        $pager= new \Illuminate\Pagination\LengthAwarePaginator(
            $result, // ページ番号で指定された表示するレコード配列
            $count2, // 検索結果の全レコード総数
            $per_page, // 1ページ当りの表示数
            $page_num, // 表示するページ
            array('path' => $request->url()) // ページャーのリンク先のURLを指定
        );

        $this->end_log();

        return $pager;
    }

    /**
     * 採用一覧取得SQL.
     *
     * @param Request  $request  リクエスト情報
     * @param Facility $facility 施設情報
     * @param int      $count    表示件数
     * @param $user ユーザ情報
     * @return
     */
    public function listWithApplySql(Request $request, int $count, $user) {
        $this->start_log();

        $today     = date('Y/m/d');
        $stop_date = date('Y/m/d');

        $sql = 'select	';
        $sql .= '    base.medicine_id	';
        $sql .= '    , base.facility_id	';
        $sql .= '    , CASE 	';
        $sql .= '        WHEN (base.pa_id is null and base.fp_id is null) 	';
        $sql .= '            THEN fp_honbu.purchase_user_group_id 	';
        $sql .= '        WHEN (base.pa_status = 100 and base.fp_id is null) 	';
        $sql .= '            THEN fp_honbu.purchase_user_group_id 	';
        $sql .= '        ELSE base.purchase_user_group_id 	';
        $sql .= '        END AS purchase_user_group_id	';
        $sql .= '    , base.pa_id ';
        $sql .= '    , base.fm_id ';
        $sql .= '    , base.fp_id	';
        $sql .= '    , fm_honbu.id fm_honbu_id	';
        $sql .= '    , fp_honbu.id fp_honbu_id	';
        $sql .= '    , base.code	';
        $sql .= '    , base.medicine_code	';
        $sql .= '    , base.sales_packaging_code	';
        $sql .= '    , base.generic_product_detail_devision	';
        $sql .= '    , base.popular_name	';
        $sql .= '    , pu.jan_code	';
        $sql .= '    , COALESCE(mk_hanbai.name,base.pa_maker_name) as hanbai_maker	';
        $sql .= '    , mk_seizo.name seizo_maker	';
        $sql .= '    , base.medicine_name	';
        $sql .= '    , base.standard_unit	';
        $sql .= '    , base.comment	';
        $sql .= '    , f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) AS status	';
        $sql .= '    , ta.name status_name	';
        $sql .= '    , base.facility_name	';
        $sql .= '    , f_show_trader_name( 	';
        $sql .= '        CASE 	';
        $sql .= '            WHEN (base.pa_id is null and base.fp_id is null) 	';
        $sql .= '                THEN fp_honbu.trader_group_name 	';
        $sql .= '            WHEN (base.pa_status = 100 and base.fp_id is null) 	';
        $sql .= '                THEN fp_honbu.trader_group_name 	';
        $sql .= '            ELSE base.trader_name 	';
        $sql .= '            END	';
        $sql .= '        , coalesce(vpry.show_sales_price_before90, 0)	';
        $sql .= '        , coalesce(vpry.show_sales_price_over90, 0)	';
        $sql .= '        , coalesce(vpry.show_purchase_price, 0)	';
        $sql .= '    ) as trader_name	';
        $sql .= '    , pu.coefficient	';
        $sql .= '    , base.pa_application_date	';
        // $sql .= '    , f_show_purchase_price( 	';
        // $sql .= '        base.pa_purchase_requested_price	';
        // $sql .= '        , coalesce(vpry.show_purchase_price, 0)	';
        // $sql .= '    ) as purchase_requested_price	';
        // $sql .= '    , f_show_purchase_price( 	';
        // $sql .= '        base.pa_purchase_estimated_price	';
        // $sql .= '        , coalesce(vpry.show_purchase_price, 0)	';
        // $sql .= '    ) as purchase_estimated_price	';
        // $sql .= '    , f_show_purchase_price( 	';
        // $sql .= '        base.purchase_price	';
        // $sql .= '        , coalesce(vpry.show_purchase_price, 0)	';
        // $sql .= '    ) as purchase_price	';
        $sql .= '    , f_show_purchase_price( ';
        $sql .= '            100 ';
        $sql .= '            , base.pa_purchase_requested_price ';
        $sql .= '            , coalesce(vpry.show_purchase_price_before60, 0) ';
        $sql .= '            , coalesce(vpry.show_purchase_price_over60, 0) ';
        $sql .= '        ) as purchase_requested_price ';
        $sql .= '    , f_show_purchase_price( ';
        $sql .= '            100 ';
        $sql .= '            , base.pa_purchase_estimated_price ';
        $sql .= '            , coalesce(vpry.show_purchase_price_before60, 0) ';
        $sql .= '            , coalesce(vpry.show_purchase_price_over60, 0) ';
        $sql .= '        ) as purchase_estimated_price ';
        $sql .= '    , f_show_purchase_price( ';
        $sql .= '            f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) ';
        $sql .= '            , base.purchase_price ';
        $sql .= '            , coalesce(vpry.show_purchase_price_before60, 0) ';
        $sql .= '            , coalesce(vpry.show_purchase_price_over60, 0) ';
        $sql .= '        ) as purchase_price ';
        $sql .= '    , f_show_sales_price( 	';
        $sql .= '          f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) 	';
        $sql .= '        , CASE 	';
        $sql .= '            WHEN (base.pa_id is null and base.fp_id is null) 	';
        $sql .= '                THEN fp_honbu.sales_price 	';
        $sql .= '            WHEN (base.pa_status = 100 and base.fp_id is null) 	';
        $sql .= '                THEN fp_honbu.sales_price 	';
        $sql .= '            ELSE base.sales_price 	';
        $sql .= '            end	';
        $sql .= '        , coalesce(vpry.show_sales_price_before90, 0)	';
        $sql .= '        , coalesce(vpry.show_sales_price_over90, 0)	';
        $sql .= '    ) as sales_price	';
        $sql .= '    , base.dispensing_packaging_code	';
        $sql .= '    , pu.hot_code	';
        $sql .= '    , base.dosage_type_division	';
        $sql .= '    , mp.price AS medicine_price	';
        $sql .= '    , mp.price * pu.coefficient AS unit_medicine_price	';
        $sql .= '    , mp.start_date AS medicine_price_start_date	';
        $sql .= '    , base.fp_start_date	';
        $sql .= '    , base.fp_end_date	';
        $sql .= '    , base.adoption_date	';
        $sql .= '    , base.adoption_stop_date	';
        $sql .= "    , case when coalesce(base.adoption_stop_date,'2100/12/31') <= CURRENT_DATE then 1 else 0 end adoption_stop_date_fg";
        $sql .= '    , base.production_stop_date	';
        $sql .= '    , base.transitional_deadline	';
        $sql .= '    , base.discontinuation_date	';
        $sql .= '    , base.discontinuing_division	';
        $sql .= '    , vprpab.status_forward	';
        $sql .= '    , vprpab.action_name_foward	';
        $sql .= '    , vprpab.status_reject	';
        $sql .= '    , vprpab.action_name_reject	';
        $sql .= '    , vprpab.status_remand	';
        $sql .= '    , vprpab.status_withdraw	';
        $sql .= '    , base.optional_key1	';
        $sql .= '    , base.optional_key2	';
        $sql .= '    , base.optional_key3	';
        $sql .= '    , base.optional_key4	';
        $sql .= '    , base.optional_key5	';
        $sql .= '    , base.optional_key6	';
        $sql .= '    , base.optional_key7	';
        $sql .= '    , base.optional_key8	';
        $sql .= '    , fm_honbu.optional_key1 as hq_optional_key1	';
        $sql .= '    , fm_honbu.optional_key2 as hq_optional_key2	';
        $sql .= '    , fm_honbu.optional_key3 as hq_optional_key3	';
        $sql .= '    , fm_honbu.optional_key4 as hq_optional_key4	';
        $sql .= '    , fm_honbu.optional_key5 as hq_optional_key5	';
        $sql .= '    , fm_honbu.optional_key6 as hq_optional_key6	';
        $sql .= '    , fm_honbu.optional_key7 as hq_optional_key7	';
        $sql .= '    , fm_honbu.optional_key8 as hq_optional_key8	';
        $sql .= '    , case 	';
        $sql .= '        when base.pa_id is not null 	';
        $sql .= '        AND base.pa_status % 10 = 0 	';
        $sql .= '        and base.pa_status <> 100 	';
        $sql .= '        and vprpab.status_forward >= 20 	';
        $sql .= '            then 1 	';
        $sql .= '        else 0 	';
        $sql .= '        end as flg_in_charge_of_adoption	';
        $sql .= '    , case 	';
        $sql .= '        when base.facility_id = usr.primary_user_group_id 	';
        $sql .= '        and base.pa_id is not null	';
        $sql .= '        and base.pa_status <> 100 	';
        $sql .= '            then 1 	';
        $sql .= '        else 0 	';
        $sql .= '        end as flg_active_own_adoption	';
        $sql .= '    , ta.color_code AS status_current_color_code	';
        $sql .= '    , ta_forward.color_code AS status_forward_color_code	';
        $sql .= '    , CASE 	';
        $sql .= '        WHEN vprpab.status_withdraw > 0 	';
        $sql .= "            THEN '#999999' 	";
        $sql .= '        ELSE NULL 	';
        $sql .= '        END AS status_withdraw_color_code	';
        $sql .= '    , vprpab.status_forward_url	';
        $sql .= '    , CASE 	';
        $sql .= '        WHEN vprpab.status_withdraw > 0 	';
        $sql .= "            THEN 'apply.withdraw' 	";
        $sql .= '        ELSE NULL 	';
        $sql .= '        END AS status_withdraw_url	';
        $sql .= '    , ta.name AS status_current_label	';
        $sql .= '    , vprpab.action_name_forward AS status_forward_label	';
        $sql .= '    , CASE 	';
        $sql .= '        WHEN vprpab.status_withdraw > 0 	';
        $sql .= "            THEN '取り下げ' 	";
        $sql .= '        ELSE NULL 	';
        $sql .= '        END AS status_withdraw_label	';
        $sql .= '    , base.pa_priority 	';
        $sql .= '    , base.pa_purchase_estimate_comment	';
        $sql .= '    , base.pa_sales_estimate_comment	';
        $sql .= '    , base.pa_user_id	';
        $sql .= '    , base.pa_user_name	';
        $sql .= '    , base.fm_group_type	';
        $sql .= '    , base.pa_created_at	';
        $sql .= '    , base.pa_updated_at	';
        $sql .= '    , case when vprpab.status_forward not in (60,70,80) then true else false end task_button_is_enabled ';
        $sql .= '    , case when vprpab.status_forward in (60,70,80) then true else false end forward_button_is_enabled	';
        $sql .= '    , case when vprpab.status_reject > 0 then true else false end reject_button_is_enabled	';
        $sql .= 'from	';
        $sql .= '    ( 	';
        $sql .= '        select	';
        $sql .= '            base1.* 	';
        $sql .= '        from	';
        $sql .= '            ( 	';
        $sql .= '                select	';
        $sql .= '                      grand.id as medicine_id	';
        $sql .= '                    , grand.name as medicine_name	';
        $sql .= '                    , grand.standard_unit	';
        $sql .= '                    , grand.search	';
        $sql .= '                    , grand.sales_packaging_code	';
        $sql .= '                    , grand.popular_name	';
        $sql .= '                    , grand.generic_product_detail_devision	';
        $sql .= '                    , grand.code	';
        $sql .= '                    , grand.medicine_code	';
        $sql .= '                    , grand.maker_id	';
        $sql .= '                    , grand.selling_agency_code	';
        $sql .= '                    , grand.dispensing_packaging_code	';
        $sql .= '                    , grand.dosage_type_division	';
        $sql .= '                    , grand.production_stop_date	';
        $sql .= '                    , grand.transitional_deadline	';
        $sql .= '                    , grand.discontinuation_date	';
        $sql .= '                    , grand.discontinuing_division	';
        $sql .= '                    , fm.id as fm_id	';
        $sql .= '                    , fm.sales_user_group_id facility_id	';
        $sql .= '                    , fm_fsr.user_group_name as facility_name	';
        $sql .= '                    , fm.comment	';
        $sql .= '                    , fm.adoption_date	';
        $sql .= '                    , fm.adoption_stop_date	';
        $sql .= '                    , fm.optional_key1	';
        $sql .= '                    , fm.optional_key2	';
        $sql .= '                    , fm.optional_key3	';
        $sql .= '                    , fm.optional_key4	';
        $sql .= '                    , fm.optional_key5	';
        $sql .= '                    , fm.optional_key6	';
        $sql .= '                    , fm.optional_key7	';
        $sql .= '                    , fm.optional_key8	';
        $sql .= '                    , fm.is_approval	';
        $sql .= '                    , gr.partner_user_group_id	';
        $sql .= '                    	';
        $sql .= '                    , COALESCE(pafp.purchase_user_group_id,0) purchase_user_group_id	';
        $sql .= '                    , COALESCE(pafp.fp_trader_group_name,pa_trader_group_name) trader_name	';
        $sql .= '                    , COALESCE(pafp.fp_purchase_price,pa_purchase_price) purchase_price	';
        $sql .= '                    , COALESCE(pafp.fp_sales_price,pa_sales_price) sales_price	';
        $sql .= '                    , pafp.fp_id	';
        $sql .= '                    , pafp.pa_id	';
        $sql .= '                    , pafp.fp_purchase_user_group_id	';
        $sql .= '                    , pafp.fp_start_date	';
        $sql .= '                    , pafp.fp_end_date	';
        $sql .= '                    , pafp.fp_purchase_price	';
        $sql .= '                    , pafp.fp_sales_price	';
        $sql .= '                    , pafp.fp_trader_group_name	';
        $sql .= '                    , pafp.pa_purchase_user_group_id	';
        $sql .= '                    , pafp.pa_status	';
        $sql .= '                    , pafp.pa_application_date	';
        $sql .= '                    , pafp.pa_purchase_requested_price	';
        $sql .= '                    , pafp.pa_purchase_estimated_price	';
        $sql .= '                    , pafp.pa_priority	';
        $sql .= '                    , pafp.pa_purchase_estimate_comment	';
        $sql .= '                    , pafp.pa_sales_estimate_comment	';
        $sql .= '                    , pafp.pa_maker_name	';
        $sql .= '                    , pafp.pa_purchase_price	';
        $sql .= '                    , pafp.pa_sales_price	';
        $sql .= '                    , pafp.pa_trader_group_name	';
        $sql .= '                    , pafp.pa_user_id	';
        $sql .= '                    , u_pa_user.name pa_user_name	';
        $sql .= '                    , ug.group_type fm_group_type	';
        $sql .= '                    , pafp.pa_created_at ';
        $sql .= '                    , pafp.pa_updated_at ';
        $sql .= '                from	';
        $sql .= '                    facility_medicines as fm 	';
        $sql .= '                    inner join f_show_records_facility('.$user->id.') as fm_fsr 	';
        $sql .= '                        on fm.sales_user_group_id = fm_fsr.user_group_id 	';
        $sql .= '                        and fm.deleted_at is null 	';
        $sql .= '                    	';
        $sql .= '                    left join 	';
        $sql .= '                    (	';
        $sql .= '                        SELECT 	';
        $sql .= '                            medicine_id	';
        $sql .= '                            , sales_user_group_id	';
        $sql .= '                            , purchase_user_group_id	';
        $sql .= '                            , fp_id	';
        $sql .= '                            , pa_id	';
        $sql .= '                            , Max(fp_purchase_user_group_id) as fp_purchase_user_group_id	';
        $sql .= '                            , Max(fp_start_date) as fp_start_date	';
        $sql .= '                            , Max(fp_end_date) as fp_end_date	';
        $sql .= '                            , Max(fp_purchase_price) as fp_purchase_price	';
        $sql .= '                            , Max(fp_sales_price) as fp_sales_price	';
        $sql .= '                            , Max(fp_trader_group_name) as fp_trader_group_name	';
        $sql .= '                            , Max(pa_purchase_user_group_id) as pa_purchase_user_group_id	';
        $sql .= '                            , Max(pa_status) as pa_status	';
        $sql .= '                            , Max(pa_application_date) as pa_application_date	';
        $sql .= '                            , Max(pa_purchase_requested_price) as pa_purchase_requested_price	';
        $sql .= '                            , Max(pa_purchase_estimated_price) as pa_purchase_estimated_price	';
        $sql .= '                            , Max(pa_priority ) as pa_priority 	';
        $sql .= '                            , Max(pa_purchase_estimate_comment) as pa_purchase_estimate_comment	';
        $sql .= '                            , Max(pa_sales_estimate_comment) as pa_sales_estimate_comment	';
        $sql .= '                            , Max(pa_maker_name) as pa_maker_name	';
        $sql .= '                            , Max(pa_purchase_price) as pa_purchase_price	';
        $sql .= '                            , Max(pa_sales_price) as pa_sales_price	';
        $sql .= '                            , Max(pa_trader_group_name) as pa_trader_group_name                        	';
        $sql .= '                            , Max(pa_user_id) as pa_user_id	';
        $sql .= '                            , Max(pa_created_at) as pa_created_at	';
        $sql .= '                            , Max(pa_updated_at) as pa_updated_at	';
        $sql .= '                        from (	';
        $sql .= '                            select	';
        $sql .= '                              fp.medicine_id	';
        $sql .= '                            , fp.sales_user_group_id	';
        $sql .= '                            , fp.purchase_user_group_id	';
        $sql .= '                            , fp.id as fp_id	';
        $sql .= '                            , fp.purchase_user_group_id as fp_purchase_user_group_id	';
        $sql .= '                            , fp.start_date as fp_start_date	';
        $sql .= '                            , fp.end_date as fp_end_date	';
        $sql .= '                            , fp.purchase_price as fp_purchase_price	';
        $sql .= '                            , fp.sales_price as fp_sales_price	';
        $sql .= '                            , fsr.trader_group_name as fp_trader_group_name	';
        $sql .= '                            , pa.id as pa_id	';
        $sql .= '                            , pa.purchase_user_group_id as pa_purchase_user_group_id	';
        $sql .= '                            , pa.status as pa_status	';
        $sql .= '                            , pa.application_date as pa_application_date	';
        $sql .= '                            , pa.purchase_requested_price as pa_purchase_requested_price	';
        $sql .= '                            , pa.purchase_estimated_price as pa_purchase_estimated_price	';
        $sql .= '                            , pa.priority as pa_priority 	';
        $sql .= '                            , pa.purchase_estimate_comment as pa_purchase_estimate_comment	';
        $sql .= '                            , pa.sales_estimate_comment as pa_sales_estimate_comment	';
        $sql .= '                            , pa.maker_name as pa_maker_name	';
        $sql .= '                            , pa.purchase_price as pa_purchase_price	';
        $sql .= '                            , pa.sales_price as pa_sales_price	';
        $sql .= '                            , null as pa_trader_group_name	';
        $sql .= '                            , pa.user_id as pa_user_id	';
        $sql .= '                            , pa.created_at as pa_created_at	';
        $sql .= '                            , pa.updated_at as pa_updated_at	';
        $sql .= '                            from facility_prices fp	';
        $sql .= '                            inner join f_show_records('.$user->id.') as fsr	';
        $sql .= '                            on fp.sales_user_group_id = fsr.user_group_id 	';
        $sql .= '                            and fp.purchase_user_group_id = fsr.trader_user_group_id 	';
        $sql .= '                            left join price_adoptions pa	';
        $sql .= '                            on fp.medicine_id = pa.medicine_id	';
        $sql .= '                            and fp.sales_user_group_id = pa.sales_user_group_id 	';
        $sql .= '                            and fp.purchase_user_group_id = pa.purchase_user_group_id 	';
        $sql .= '                            and pa.status = 100	';
        $sql .= '                            and pa.deleted_at is null	';
        $sql .= '                            where fp.start_date <= CURRENT_DATE and fp.end_date >= CURRENT_DATE and fp.deleted_at is null    ';
        $sql .= '                                                      	';
        $sql .= '                            union	';
        $sql .= '                            select	';
        $sql .= '                              pa.medicine_id	';
        $sql .= '                            , pa.sales_user_group_id	';
        $sql .= '                            , pa.purchase_user_group_id	';
        $sql .= '                            , null as fp_id	';
        $sql .= '                            , null as fp_purchase_user_group_id	';
        $sql .= '                            , null as fp_start_date	';
        $sql .= '                            , null as fp_end_date	';
        $sql .= '                            , null as fp_purchase_price	';
        $sql .= '                            , null as fp_sales_price	';
        $sql .= '                            , null as fp_trader_group_name	';
        $sql .= '                            , pa.id as pa_id	';
        $sql .= '                            , pa.purchase_user_group_id as pa_purchase_user_group_id	';
        $sql .= '                            , pa.status as pa_status	';
        $sql .= '                            , pa.application_date as pa_application_date	';
        $sql .= '                            , pa.purchase_requested_price as pa_purchase_requested_price	';
        $sql .= '                            , pa.purchase_estimated_price as pa_purchase_estimated_price	';
        $sql .= '                            , pa.priority as pa_priority 	';
        $sql .= '                            , pa.purchase_estimate_comment as pa_purchase_estimate_comment	';
        $sql .= '                            , pa.sales_estimate_comment as pa_sales_estimate_comment	';
        $sql .= '                            , pa.maker_name as pa_maker_name	';
        $sql .= '                            , pa.purchase_price as pa_purchase_price	';
        $sql .= '                            , pa.sales_price as pa_sales_price	';
        $sql .= '                            , fsr2.trader_group_name as pa_trader_group_name	';
        $sql .= '                            , pa.user_id as pa_user_id	';
        $sql .= '                            , pa.created_at as pa_created_at	';
        $sql .= '                            , pa.updated_at as pa_updated_at	';
        $sql .= '                        	';
        $sql .= '                            from price_adoptions pa	';
        $sql .= '                            inner join f_show_records('.$user->id.') as fsr2	';
        $sql .= '                            on  pa.sales_user_group_id = fsr2.user_group_id 	';
        $sql .= '                            and pa.purchase_user_group_id = fsr2.trader_user_group_id 	';
        $sql .= '                        	';
        $sql .= '                            where pa.status <100 and pa.deleted_at is null';
        $sql .= '                        ) as t1	';
        $sql .= '                        group by medicine_id, sales_user_group_id,purchase_user_group_id,fp_id,pa_id	';
        $sql .= '	';
        $sql .= '                    ) pafp	';
        $sql .= '                    on fm.medicine_id = pafp.medicine_id 	';
        $sql .= '                    and fm.sales_user_group_id = pafp.sales_user_group_id 	';
        $sql .= '	';
        $sql .= '                    left join medicines as grand 	';
        $sql .= '                        on fm.medicine_id = grand.id 	';
        $sql .= '                        and grand.deleted_at is null 	';
        $sql .= '                    left join group_relations as gr 	';
        $sql .= '                        on fm.sales_user_group_id = gr.user_group_id 	';
        $sql .= '                        and gr.deleted_at is null	';
        $sql .= '                    LEFT JOIN users u_pa_user	';
        $sql .= '                        ON pafp.pa_user_id = u_pa_user.id	';
        $sql .= '                    LEFT JOIN user_groups ug	';
        $sql .= '                        ON fm.sales_user_group_id = ug.id	';
        $sql .= '            ) as base1 	';
        $sql .= '             inner join f_show_records('.$user->id.') as fsr3 	';
        $sql .= '             on base1.facility_id = fsr3.user_group_id 	';
        $sql .= '             and base1.purchase_user_group_id = fsr3.trader_user_group_id 	';
        $sql .= '        union all 	';
        $sql .= '        select	';
        $sql .= '            grand_p.id as medicine_id	';
        $sql .= '            , grand_p.name as medicine_name	';
        $sql .= '            , grand_p.standard_unit	';
        $sql .= '            , grand_p.search	';
        $sql .= '            , grand_p.sales_packaging_code	';
        $sql .= '            , grand_p.popular_name	';
        $sql .= '            , grand_p.generic_product_detail_devision	';
        $sql .= '            , grand_p.code	';
        $sql .= '            , grand_p.medicine_code	';
        $sql .= '            , grand_p.maker_id	';
        $sql .= '            , grand_p.selling_agency_code	';
        $sql .= '            , grand_p.dispensing_packaging_code	';
        $sql .= '            , grand_p.dosage_type_division	';
        $sql .= '            , grand_p.production_stop_date	';
        $sql .= '            , grand_p.transitional_deadline	';
        $sql .= '            , grand_p.discontinuation_date	';
        $sql .= '            , grand_p.discontinuing_division	';
        $sql .= '            , null as fm_id	';
        $sql .= '            , null as facility_id	';
        $sql .= '            , null as facility_name	';
        $sql .= '            , null as comment	';
        $sql .= '            , null AS adoption_date	';
        $sql .= '            , null AS adoption_stop_date	';
        $sql .= '            , null AS optional_key1	';
        $sql .= '            , null AS optional_key2	';
        $sql .= '            , null AS optional_key3	';
        $sql .= '            , null AS optional_key4	';
        $sql .= '            , null AS optional_key5	';
        $sql .= '            , null AS optional_key6	';
        $sql .= '            , null AS optional_key7	';
        $sql .= '            , null AS optional_key8	';
        $sql .= '            , null as is_approval	';
        $sql .= '            , gr_p.partner_user_group_id 	';
        $sql .= '            , null as purchase_user_group_id	';
        $sql .= '            , null as trader_name	';
        $sql .= '            , null as purchase_price	';
        $sql .= '            , null as sales_price	';
        $sql .= '            , null as fp_id	';
        $sql .= '            , null as pa_id	';
        $sql .= '            , null as fp_purchase_user_group_id	';
        $sql .= '            , null as fp_start_date	';
        $sql .= '            , null as fp_end_date	';
        $sql .= '            , null as fp_purchase_price	';
        $sql .= '            , null as fp_sales_price	';
        $sql .= '            , null as fp_trader_group_name	';
        $sql .= '            , null as pa_purchase_user_group_id	';
        $sql .= '            , null as pa_status	';
        $sql .= '            , null as pa_application_date	';
        $sql .= '            , null as pa_purchase_requested_price	';
        $sql .= '            , null as pa_purchase_estimated_price	';
        $sql .= '            , null as pa_priority	';
        $sql .= '            , null as pa_purchase_estimate_comment	';
        $sql .= '            , null as pa_sales_estimate_comment	';
        $sql .= '            , null as pa_maker_name	';
        $sql .= '            , null as pa_purchase_price	';
        $sql .= '            , null as pa_sales_price	';
        $sql .= '            , null as pa_trader_group_name	';
        $sql .= '            , null as pa_user_id	';
        $sql .= '            , null as pa_user_name	';
        $sql .= '            , null as fm_group_type ';
        $sql .= '            , null as pa_created_at ';
        $sql .= '            , null as pa_updated_at ';
        $sql .= '        from	';
        $sql .= '            medicines as grand_p 	';
        $sql .= '            left join users as usr_p 	';
        $sql .= '                on usr_p.id = '.$user->id ;
        $sql .= '            left join group_relations as gr_p 	';
        $sql .= '                on gr_p.user_group_id = usr_p.primary_user_group_id 	';
        $sql .= '                and gr_p.deleted_at is null 	';
        $sql .= '            left outer join facility_medicines as fm_p 	';
        $sql .= '                on grand_p.id = fm_p.medicine_id 	';
        $sql .= '                and fm_p.sales_user_group_id = gr_p.user_group_id 	';
        $sql .= '                and fm_p.deleted_at is null 	';
        $sql .= '        where	';
        $sql .= '            grand_p.deleted_at is null 	';
        $sql .= '            and fm_p.medicine_id is null	';
        $sql .= '    ) as base 	';
        $sql .= '    left join facility_medicines as fm_honbu 	';
        $sql .= '        on base.medicine_id = fm_honbu.medicine_id 	';
        $sql .= '        and base.partner_user_group_id = fm_honbu.sales_user_group_id 	';
        $sql .= '        and fm_honbu.deleted_at is null 	';
        $sql .= '    left join ( 	';
        $sql .= '        select	';
        $sql .= '            fp2.*	';
        $sql .= '            , fsr2.user_group_id	';
        $sql .= '            , fsr2.user_group_name	';
        $sql .= '            , fsr2.trader_user_group_id	';
        $sql .= '            , fsr2.trader_group_name 	';
        $sql .= '        from	';
        $sql .= '            facility_prices as fp2 	';
        $sql .= '            inner join f_show_records('.$user->id.') as fsr2 	';
        $sql .= '                on fp2.sales_user_group_id = fsr2.user_group_id 	';
        $sql .= '                and fp2.purchase_user_group_id = fsr2.trader_user_group_id 	';
        $sql .= '                and fp2.deleted_at is null	';
        $sql .= '    ) as fp_honbu 	';
        $sql .= '        on base.medicine_id = fp_honbu.medicine_id 	';
        $sql .= '        and base.partner_user_group_id = fp_honbu.sales_user_group_id 	';
        $sql .= '        and fp_honbu.start_date <= CURRENT_DATE 	';
        $sql .= '        and fp_honbu.end_date >= CURRENT_DATE 	';
        $sql .= "        and coalesce(fm_honbu.adoption_stop_date,'2999/12/31') > CURRENT_DATE 	";
        $sql .= '        and case 	';
        $sql .= '            when (base.pa_id is null and base.fp_id is null) 	';
        $sql .= '            OR (base.pa_status = 100 AND base.fp_id IS NULL) 	';
        $sql .= '                then 1 = 1 	';
        $sql .= '            else base.purchase_user_group_id = fp_honbu.trader_user_group_id 	';
        $sql .= '            end 	';
        $sql .= '    left join pack_units as pu 	';
        $sql .= '        on base.medicine_id = pu.medicine_id 	';
        $sql .= '    LEFT JOIN medicine_prices AS mp 	';
        $sql .= '        ON base.medicine_id = mp.medicine_id 	';
        $sql .= '        and mp.end_date >= CURRENT_DATE 	';
        $sql .= '        and mp.start_date <= CURRENT_DATE 	';
        $sql .= '        and mp.deleted_at is NULL 	';
        $sql .= '    left join makers as mk_seizo 	';
        $sql .= '        on base.maker_id = mk_seizo.id 	';
        $sql .= '        and mk_seizo.deleted_at is null 	';
        $sql .= '    left join makers as mk_hanbai 	';
        $sql .= '        on base.selling_agency_code = mk_hanbai.id 	';
        $sql .= '        and mk_hanbai.deleted_at is null 	';
        $sql .= '    LEFT JOIN users AS usr 	';
        $sql .= '        ON usr.id = '.$user->id;
        $sql .= '    left join f_privileges_row_price_adoptions_bystate('.$user->id.') as vprpab 	';
        $sql .= '        on vprpab.sales_user_group_id = coalesce( 	';
        $sql .= '            base.facility_id	';
        $sql .= '            , usr.primary_user_group_id	';
        $sql .= '        ) 	';
        $sql .= '        and vprpab.purchase_user_group_id = coalesce(base.purchase_user_group_id, 0) 	';
        $sql .= '        and vprpab.status = f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) 	';
        $sql .= '    left join f_privileges_row_price_adoptions('.$user->id.') as vpry 	';
        $sql .= '        on vpry.sales_user_group_id = coalesce( 	';
        $sql .= '            base.facility_id	';
        $sql .= '            , usr.primary_user_group_id	';
        $sql .= '        ) 	';
        $sql .= '        and vpry.purchase_user_group_id = coalesce(base.purchase_user_group_id, 0) 	';
        $sql .= '    left join tasks ta 	';
        $sql .= '        on f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) = ta.status 	';
        $sql .= '        and ta.apply_payment_flg = 0 	';
        $sql .= '        and ta.deleted_at is null 	';
        $sql .= '    LEFT JOIN tasks ta_forward 	';
        $sql .= '        ON vprpab.status_forward = ta_forward.status 	';
        $sql .= '        AND ta_forward.apply_payment_flg = 0 	';
        $sql .= '        AND ta_forward.deleted_at IS NULL 	';
        $sql .= '	where	';
        $sql .= '	1 = 1	';


        // 私が担当
        if ($request->simple_search == 'my_charge'){
            $sql .= ' and ';
            $sql .= ' (base.pa_id is not null ';
            $sql .= ' and base.pa_status % 10 = 0 ';
            $sql .= ' and base.pa_status <> 100 ';
            $sql .= ' and vprpab.status_forward >= 20) ';
        }
        
        // 私が担当 関連申請含む
        if ($request->simple_search == 'my_charge_including_related'){
            $sql .= ' and base.pa_id in (select pa_id from f_price_adoptions_my_charge_and_source_paid('.$user->id.'))';
        }

        // 私が申請
        if ($request->simple_search == 'my_apply'){
            $sql .= ' and (base.facility_id = usr.primary_user_group_id ';
            $sql .= ' and base.pa_id is not null ';
            $sql .= ' and base.pa_status <> 100) ';
        }

        // 申請進行中
        if ($request->simple_search == 'is_applying'){
            $sql .= ' and base.pa_status in (20,30,40,50,60,70,80,90)';
        }

        // 採用済み
        if ($request->simple_search == '100'){
            $sql .= '     and f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) = 100 ';
        }

        // 採用可
        if ($request->simple_search == '91'){
            $sql .= '     and f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) = 91 ';
        }

        // 要受諾依頼
        if ($request->simple_search == 'need_request'){
            $result = \DB::select("select f_2buisinessday_before() as before2day");
            $sql .= "   and (CAST(base.pa_created_at AS DATE) <='". $result[0]->before2day. "'";  //2営業日前以前
            $sql .= "   and base.fm_group_type = '文化連'";
            $sql .= '    and f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) in (40,50)) ';
        }

        // 要見積提出
        if ($request->simple_search == 'need_estimate'){
            $result = \DB::select("select f_2buisinessday_before() as before2day");

            $sql .= "   and ((CAST(base.pa_created_at AS DATE) <='". $result[0]->before2day. "'";  //2営業日前以前
            $sql .= "   and base.fm_group_type in ('病院','本部')";
            $sql .= '    and f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) in (30,40,50,60)) ';
            $sql .= '    or ( ';
            $sql .= "    base.fm_group_type in ('病院','本部')";
            $sql .= '    and f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) = 70)) ';
        }

        // 詳細なｽﾃｰﾀｽ
        if (!empty($request->status)) {
            $sql .= 'and f_calculate_status(base.fm_id,base.is_approval,base.fp_id,fp_honbu.id,base.pa_id,base.pa_status) in (' . implode(',', $request->status) . ')';
        }

        /* 採用中止品は常にグレー背景で表示することにしたので、検索条件をつぶす
        //採用中止日
        if (empty($request->stop_date)) {
            $sql .= " and coalesce(base.adoption_stop_date,'2999/12/31') >= '" . $stop_date . "'";
        }
        */

        // 施設
        if (!empty($request->user_group)) {
            $sql .= ' and base.facility_id in (' . implode(',', $request->user_group) . ')';
        }

        // 医薬品CD
        if (!empty($request->code)) {
            $sql .= " and base.code like '%" . mb_convert_kana($request->code, 'kvas') . "%'";
        }

        // 一般名
        if (!empty($request->popular_name)) {
            $sql .= " and base.popular_name like '%" . mb_convert_kana($request->popular_name, 'KVAS') . "%'";
        }

        // 包装薬価係数
        if (!empty($request->coefficient)) {
            $sql .= ' and pu.coefficient = ' . mb_convert_kana($request->coefficient, 'kvas');
        }

        // 販売中止日
        if (empty($request->discontinuation_date)) {
            $sql .= " and base.discontinuation_date >= '" . date('Y-m-d H:i:s') . "'";
        }

        $search_words = array();

        // 全文検索
        if (!empty($request->search)) {
            $search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));

            foreach ($search_words as $word) {
                $sql .= " and base.search like '%" . $word . "%'";
            }
        }

        // 剤型区分
        if (!empty($request->dosage_type_division)) {
            $sql .= ' and base.dosage_type_division = ' . $request->dosage_type_division;
        }

        // 先発後発品区分
        if ($request->generic_product_detail_devision !== null && strlen($request->generic_product_detail_devision) > 0) {
            $sql .= " and base.generic_product_detail_devision = '" . $request->generic_product_detail_devision . "'";
        }

        // 優先度
        if (!empty($request->priority)) {
            $sql .= ' and base.pa_priority = ' . $request->priority;
        }

        //項目
        for ($i = 1; $i <= 8; $i++) {
            $key_name = 'optional_key' . $i;

            if (!empty($request->{$key_name})) {
                $sql .= ' and base.' . $key_name . " @@'" . $request->{$key_name} . "' ";
            }
        }

        //項目
        for ($i = 1; $i <= 8; $i++) {
            $key_name    = 'optional_key' . $i;
            $hq_key_name = 'hq_optional_key' . $i;

            if (!empty($request->{$hq_key_name})) {
                $sql .= ' and fm_honbu.' . $key_name . " @@'" . $request->{$hq_key_name} . "' ";
            }
        }

        $this->end_log();

        return $sql;
    }

    // 薬価の取得
    public function medicinePrice() {
        $this->start_log();

        $result = $this
            ->hasOne('App\Model\MedicinePrice')
            ->where('medicine_prices.end_date', '>=', date('Y-m-d H:i:s'))
            ->where('medicine_prices.start_date', '<=', date('Y-m-d H:i:s'));

        $this->end_log();

        return $result;
    }

    // 薬価の取得
    public function getMedicinePrices($medicine_id) {
        $this->start_log();

        $result = $this
            ->where('id', $medicine_id)
            ->where('medicine_id', $medicine_id)
            ->first();

        $this->end_log();

        return $result;
    }

    // 包装マスタの取得
    public function packUnit() {
        $this->start_log();

        $result = $this->hasOne('App\Model\PackUnit');

        $this->end_log();

        return $result;
    }

    // 製造メーカーの取得
    public function maker() {
        $this->start_log();

        $result = $this->belongsTo('App\Model\Maker');

        $this->end_log();

        return $result;
    }

    // 販売メーカーの取得
    public function sellingMaker() {
        $this->start_log();

        $result = $this->belongsTo('App\Model\Maker', 'selling_agency_code');

        $this->end_log();

        return $result;
    }

    // 毒物区分
    public function getDangerPoison() {
        $this->start_log();

        $text = array();

        if ($this->blood_product_flag) {
            $text[] = $this->blood_product_flag;
        }

        if ($this->biological_product_classification == '1') {
        } elseif ($this->biological_product_classification == '2') {
            $text[] = self::BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT2;
        } elseif ($this->biological_product_classification == '3') {
            $text[] = self::BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT3;
        }

        for ($i = 1; $i <= 5; $i++) {
            $category_name = 'danger_poison_category' . $i;

            if ($this->{$category_name}) {
                $text[] = $this->{$category_name};
            }
        }

        if ($this->stimulant_material_flag) {
            $text[] = $this->stimulant_material_flag;
        }

        $this->end_log();

        return implode('・', $text);
    }

    // 承認申請一覧
    public function listWithApplyDetail(Request $request, $medicine_id, $user) {
        $this->start_log();

        //過去3年
        $nendo = $this->getSearchFiscalYearHistory();

        $tmp_date     = new \DateTime($nendo[1]);
        $before3Start = $nendo[2];
        $tmp_date     = $tmp_date->modify('-1 days');
        $before3End   = $tmp_date->format('Y/m/d');

        $tmp_date     = new \DateTime($nendo[0]);
        $before2Start = $nendo[1];
        $tmp_date     = $tmp_date->modify('-1 days');
        $before2End   = $tmp_date->format('Y/m/d');

        $before1Start = $nendo[0];

        $today      = date('Y/m/d');
        $start_date = '04/01';

        $start_year = date('Y') . '/' . $start_date;

        if (strtotime($today) >= strtotime($start_year)) {
            // 2019/4/20 >= 2019/04/01 = 2019
            $year = date('Y') + 1;
        } else {
            // 2019/3/20 < 2019/04/01 = 2018
            $year = date('Y');
        }

        $before1End = $year . '/03/31';

        $sql  = '';
        $sql .= ' select ';
        $sql .= '      user_groups.name as facility_name ';
        $sql .= '     ,facility_medicines.adoption_date ';
        $sql .= '     ,facility_medicines.adoption_stop_date ';
        $sql .= '     ,facility_medicines.sales_user_group_id ';
        $sql .= '     ,coalesce(before3Detail.cnt,0) as before3cnt ';
        $sql .= '     ,coalesce(before2Detail.cnt,0) as before2cnt ';
        $sql .= '     ,coalesce(before1Detail.cnt,0) as before1cnt ';
        $sql .= ' from ';
        $sql .= '     facility_medicines ';
        $sql .= '      inner join ( ';
        $sql .= '          select distinct ';
        $sql .= '               b.id ';
        $sql .= '              ,b.name ';
        $sql .= '              ,b.disp_order ';
        $sql .= '          from ';
        $sql .= '              user_groups as b ';
        $sql .= '              inner join user_group_relations as c ';
        $sql .= '                  on c.user_group_id = b.id ';
        $sql .= '                  and  c.deleted_at is null ';
        $sql .= '          where ';
        $sql .= '              c.user_id = ' . $user->id;
        $sql .= '      ) as user_groups ';
        $sql .= '          on facility_medicines.sales_user_group_id = user_groups.id ';
        $sql .= '      left join (';
        $sql .= '          select ';
        $sql .= '              sales_user_group_id ';
        $sql .= '             ,medicine_id ';
        $sql .= '             ,sum(transact_details.quantity) as cnt ';
        $sql .= '          from ';
        $sql .= '              transact_details ';
        $sql .= '          where ';
        $sql .= '              deleted_at is null ';
        $sql .= "              and claim_date >= '" . $before3Start . "'";
        $sql .= "              and claim_date <= '" . $before3End . "'";
        $sql .= '          group by ';
        $sql .= '              sales_user_group_id ';
        $sql .= '             ,medicine_id ';
        $sql .= '      ) as before3Detail ';
        $sql .= '          on facility_medicines.sales_user_group_id=before3Detail.sales_user_group_id ';
        $sql .= '          and facility_medicines.medicine_id=before3Detail.medicine_id ';
        $sql .= '      left join (';
        $sql .= '          select ';
        $sql .= '              sales_user_group_id ';
        $sql .= '             ,medicine_id ';
        $sql .= '             ,sum(transact_details.quantity) as cnt ';
        $sql .= '          from ';
        $sql .= '              transact_details ';
        $sql .= '          where ';
        $sql .= '              deleted_at is null ';
        $sql .= "              and claim_date >= '" . $before2Start . "'";
        $sql .= "              and claim_date <= '" . $before2End . "'";
        $sql .= '          group by ';
        $sql .= '              sales_user_group_id ';
        $sql .= '             ,medicine_id ';
        $sql .= '      ) as before2Detail ';
        $sql .= '          on facility_medicines.sales_user_group_id=before2Detail.sales_user_group_id ';
        $sql .= '          and facility_medicines.medicine_id=before2Detail.medicine_id ';
        $sql .= '      left join (';
        $sql .= '          select ';
        $sql .= '              sales_user_group_id ';
        $sql .= '             ,medicine_id ';
        $sql .= '             ,sum(transact_details.quantity) as cnt ';
        $sql .= '          from ';
        $sql .= '              transact_details ';
        $sql .= '          where ';
        $sql .= '              deleted_at is null ';
        $sql .= "              and claim_date >= '" . $before1Start . "'";
        $sql .= "              and claim_date <= '" . $before1End . "'";
        $sql .= '          group by ';
        $sql .= '              sales_user_group_id ';
        $sql .= '             ,medicine_id ';
        $sql .= '      ) as before1Detail ';
        $sql .= '          on facility_medicines.sales_user_group_id=before1Detail.sales_user_group_id ';
        $sql .= '          and facility_medicines.medicine_id=before1Detail.medicine_id ';

        $sql .= ' where ';
        $sql .= '     facility_medicines.medicine_id = ' . $medicine_id;
        $sql .= '     and facility_medicines.is_approval = true';
        $sql .= '     and facility_medicines.deleted_at is null ';

        $count_sql = ' select count(*) from (' . $sql . ') as tmp ';
        $all_count = \DB::select($count_sql);

        $count=$all_count[0]->count;

        $sql .= ' order by ';
        $sql .= '       user_groups.disp_order, user_groups.name ';
        //$sql .= "     user_groups.name ";
        /*
        if (!isset($request->page_count)) {
            $offset = 0;
        } else {
            if ($request->page == 0) {
                $offset = 0;
            } else {
                $offset=($request->page - 1) * $request->page_count;
            }
        }

        $per_page = $request->page_count ?? 1;
        $sql .= ' limit ' . $per_page . ' offset ' . $offset;
        $all_rec = \DB::select($sql);
        $result  = self::query()->hydrate($all_rec);

        // ページ番号が指定されていなかったら１ページ目
        $page_num = $request->page ?? 1;
        // ページ番号に従い、表示するレコードを切り出す
        $disp_rec = array_slice($result->all(), ($page_num - 1) * $per_page, $per_page);
*/
        $per_page = $request->page_count ?? 1;
        $all_rec = \DB::select($sql);
        $result  = self::query()->hydrate($all_rec);
        $page_num =1;

        // ページャーオブジェクトを生成
        $pager= new \Illuminate\Pagination\LengthAwarePaginator(
            $result, // ページ番号で指定された表示するレコード配列
            $count, // 検索結果の全レコード総数
            $per_page, // 1ページ当りの表示数
            $page_num, // 表示するページ
            array('path' => $request->url()) // ページャーのリンク先のURLを指定
        );

        $this->end_log();

        return $pager;
    }

    // 一覧用のsalesPrice取得
    public function getSalesPrice() {
        if (isset($this->sales_price)) {
            return $this->sales_price;
        }

        $fp = $this->getFacilityPrice();

        if (isset($fp)) {
            $this->end_log();
            return $fp->sales_price;
        }
    }

    // 一覧用のsalesPrice取得
    public function getTraderName() {
        $this->start_log();

        if (empty($this->trader_name)) {
            $fp = $this->getFacilityPrice();

            if ($fp !== null) {
                $trader = Facility::find($fp->trader_id);

                $this->end_log();

                return !empty($trader->name) ? $trader->name : '';
            }
            $this->end_log();

            return '';
        }

        $this->end_log();

        return $this->trader_name;
    }

    // 標準薬品一覧
    public function listWithMedicines(Request $request, $count) {
        $this->start_log();

        // サブクエリ
        $query = $this
            ->select(
                'medicines.id as medicine_id',
                'medicines.kon_medicine_id',
                'pack_units.jan_code',
                'medicines.name as medicine_name',
                'medicines.standard_unit as standard_unit',
                'makers.name as maker_name',
                'medicine_prices.price as price',
                'medicine_effects.name as medicine_effect'
            )
            ->join('pack_units', 'pack_units.medicine_id', '=', 'medicines.id')
            ->join('makers', 'makers.id', '=', 'medicines.maker_id')
            ->join('medicine_prices', 'medicine_prices.medicine_id', '=', 'medicines.id')
            ->leftJoin('medicine_effects', 'medicine_effects.id', '=', 'medicines.medicine_effet_id');
        // 検索条件
        // JANコード
        if (!empty($request->jan_code)) {
            $query->where('jan_code', $request->jan_code);
        }
        // 一般名
        if (!empty($request->popular_name)) {
            $query->where('popular_name', 'like', '%' . $request->popular_name . '%');
        }
        // 商品名
        if (!empty($request->name)) {
            $query->where('medicines.name', 'like', '%' . $request->name . '%');
        }
        // 剤型区分
        if (!empty($request->dosage_type_division)) {
            $query->where('dosage_type_division', $request->dosage_type_division);
        }
        // オーナー区分
        if (!empty($request->owner_classification)) {
            $query->where('owner_classification', $request->owner_classification);
        }
        // 全文検索
        if (!empty($request->search)) {
            $query->where('medicines.search', 'like', '%' . $request->search . '%');
        }

        $this->end_log();

        return $query->paginate($count);
    }

    // 申請ステータスに応じた権限を返す
    public function getPrivilegesApply($user_id, $user_group_id, $status, $purchase_user_group_id) {
        $this->start_log();

        $sql  = ' select ';
        $sql .= '      status_forward ';
        $sql .= '      ,action_name_foward ';
        $sql .= '      ,status_reject ';
        $sql .= '      ,action_name_reject ';
        $sql .= '      ,status_remand ';
        $sql .= '      ,status_withdraw ';
        $sql .= ' from ';
        $sql .= '    f_privileges_row_price_adoptions_bystate(' . $user_id . ') as a ';
        $sql .= '  where ';
        $sql .= '      a.user_id = ' . $user_id;
        $sql .= '      and a.sales_user_group_id =' . $user_group_id;
        $sql .= '      and a.purchase_user_group_id =' . $purchase_user_group_id;
        $sql .= '      and a.status =' . $status;

        $all_rec = \DB::select($sql);

        if (empty($all_rec)) {
            $all_rec = array();

            $stdObj                     = new \stdClass();
            $stdObj->status_forward     = -1;
            $stdObj->action_name_foward = null;
            $stdObj->status_reject      = -1;
            $stdObj->action_name_reject = null;
            $stdObj->status_remand      = -1;
            $stdObj->status_withdraw    = -1;

            $all_rec[] = $stdObj;
        }

        $this->end_log();

        return $all_rec;
    }

    // 申請ステータスに応じた業者名を返す
    public function getPrivilegesTrader($status, $trader_name, $show_sales_price_before90, $show_sales_price_over90, $show_purchase_price) {
        $this->start_log();

        $sql  = ' select ';
        $sql .= '      f_show_trader_name2 as f_show_trader_name';
        $sql .= ' from ';
        $sql .= '     f_show_trader_name2(' . $status . ",'" . $trader_name . "'," . $show_sales_price_before90 . ',' . $show_sales_price_over90 . ',' . $show_purchase_price . ')';

        $all_rec = \DB::select($sql);

        if (empty($all_rec)) {
            $all_rec = array();

            $stdObj                     = new \stdClass();
            $stdObj->f_show_trader_name = null;

            $all_rec[] = $stdObj;
        }

        $this->end_log();

        return $all_rec;
    }

    // 申請可能なグループを返す
    public function getApplyGroupList($user_id, $status) {
        $this->start_log();

        $sql  = ' select ';
        $sql .= '      t.sales_user_group_id as user_group_id ';
        $sql .= '     ,max(ug.name) as name';
        $sql .= ' from ';
        $sql .= ' (select ';
        $sql .= '      sales_user_group_id ';
        $sql .= '     ,status_forward ';
        $sql .= '     ,action_name_foward ';
        $sql .= '      ,status_reject';
        $sql .= '      ,action_name_reject';
        $sql .= '      ,status_remand';
        $sql .= '      ,status_withdraw';
        $sql .= '  from ';
        $sql .= '     f_privileges_row_price_adoptions_bystate(' . $user_id . ') as f  ';
        $sql .= '  where ';
        $sql .= '      user_id = ' . $user_id;
        $sql .= '      and status = ' . $status;
        $sql .= '      and status_forward > 0 ';
        $sql .= ' ) as t ';
        $sql .= ' inner join user_groups ug';
        $sql .= '     on t.sales_user_group_id = ug.id ';
        $sql .= '     and ug.deleted_at is null ';
        $sql .= ' group by ';
        $sql .= '      t.sales_user_group_id ';
        $sql .= '     ,ug.id ';
        $sql .= ' order by ug.disp_order';


        $all_rec = \DB::select($sql);

        if (empty($all_rec)) {
            $all_rec = array();
        }

        $this->end_log();

        return $all_rec;
    }

    // 採用申請 メール送付先取得
    public function getEmailApply($status, $user_group_id, $exit_user_group_id, $purchase_user_group_id) {
        $this->start_log();

        $user = Auth::user();

        if (empty($purchase_user_group_id)) {
            $purchase_user_group_id = 0;
        }

        $sql  = ' select ';
        $sql .= '     a.email ';
        $sql .= ' from  ';
        $sql .= ' (select ';
        $sql .= '      a.status_forward ';
        $sql .= '      ,a.action_name_foward ';
        $sql .= '      ,a.status_reject';
        $sql .= '      ,a.action_name_reject';
        $sql .= '      ,a.status_remand';
        $sql .= '      ,a.status_withdraw';
        $sql .= '     ,b.email ';
        $sql .= '  from ';
        $sql .= '    v_privileges_row_price_adoptions_bystate_20201002 as a ';
        // $sql .= '    f_privileges_row_price_adoptions_bystate(' . $user->id . ') as a ';
        $sql .= '     inner join users as b ';
        $sql .= '          on a.user_id = b.id';
        $sql .= '          and b.deleted_at is null ';
        $sql .= '  where ';
        $sql .= '      b.is_adoption_mail = true';
        $sql .= '      and a.sales_user_group_id = ' . $user_group_id;
        $sql .= '      and a.purchase_user_group_id = ' . $purchase_user_group_id;
        $sql .= '      and a.status = ' . $status;
        $sql .= '      and a.status_forward > 0 ';


        if (!empty($exit_user_group_id)) {
            $sql .= '      and b.primary_user_group_id <> ' . $exit_user_group_id;
        }

        $sql .= ' ) as a ';
        $sql .= ' where  ';
        $sql .= ' 1=1 ';
        // $sql .= '     a.status_forward > 0 ';

        if (env('ENV_TYPE') === 'staging') {
            $sql .= " and (a.email like '%bunkaren.or.jp' ";
            $sql .= "   or a.email like '%k-on.co.jp' ";
            $sql .= "   or a.email like '%example.com') ";
        }

        $sql .= ' group by  ';
        $sql .= '     a.email ';
        $all_rec = \DB::select($sql);

        if (empty($all_rec)) {
            $all_rec = array();

            $stdObj        = new \stdClass();
            $stdObj->email = '';

            $all_rec[] = $stdObj;
        }
        $this->end_log();

        return $all_rec;
    }

    // 申請ステータスに売上・仕入表示権限を返す
    public function getShowPriceApply($user_id, $user_group_id, $status) {
        $this->start_log();

        if (empty($status)) {
            $status = 1;
        }

        $sql  = ' select ';
        $sql .= '     case ';
        $sql .= '          when ' . $status . ' <= ' . Task::STATUS_APPROVAL_WAITING . ' then';
        $sql .= '              coalesce(vpry.show_sales_price_before90,0) ';
        $sql .= '          else ';
        $sql .= '              coalesce(vpry.show_sales_price_over90,0) ';
        $sql .= '     end as sales_price_kengen ';
        $sql .= '    ,coalesce(vpry.show_purchase_price,0)     as purchase_price_kengen';

        // 業者名判定用に追加
        $sql .= '    ,coalesce(vpry.show_sales_price_before90,0) as show_sales_price_before90';
        $sql .= '    ,coalesce(vpry.show_sales_price_over90,0) as show_sales_price_over90';
        $sql .= '    ,coalesce(vpry.show_purchase_price,0) as show_purchase_price ';
        // TODO 業者用の価格の権限が独自にいるならここに追加する必要がある
        // 枠権限２つ
        $sql .= '    ,coalesce(vpry.show_sales_price_waku, 0) as sales_price_waku_kengen';
        $sql .= '    ,coalesce(vpry.show_purchase_price_waku, 0) as purchase_price_waku_kengen';
        // 業者の単価制御用 30 40 50 70 80 90
        $sql .= '    ,coalesce(vpry.arrow30,0)     as arrow30';
        $sql .= '    ,coalesce(vpry.arrow40,0)     as arrow40';
        $sql .= '    ,coalesce(vpry.arrow50,0)     as arrow50';
        $sql .= '    ,coalesce(vpry.arrow60,0)     as arrow60';
        $sql .= '    ,coalesce(vpry.arrow70,0)     as arrow70';
        $sql .= '    ,coalesce(vpry.arrow80,0)     as arrow80';
        $sql .= '    ,coalesce(vpry.arrow90,0)     as arrow90';
        // 採用中止日と一番下のコメント欄及び業者プルダウンの入力権限(採用マスタ編集権限)
        $sql .= '    ,coalesce(vpry.edit_facility_medicine,0)     as edit_facility_medicine';

        $sql .= ' from ';
        $sql .= '      v_privileges_row_yakuhinshinsei2 as vpry ';
        $sql .= ' where ';
        $sql .= '      vpry.user_id = ' . $user_id;
        $sql .= '      and vpry.user_group_id =' . $user_group_id;

        $all_rec = \DB::select($sql);

        if (empty($all_rec)) {
            $all_rec = array();

            $stdObj                             = new \stdClass();
            $stdObj->sales_price_kengen         = 0;
            $stdObj->purchase_price_kengen      = 0;
            $stdObj->show_sales_price_before90  = 0;
            $stdObj->show_sales_price_over90    = 0;
            $stdObj->show_purchase_price        = 0;
            $stdObj->sales_price_waku_kengen    = 0;
            $stdObj->purchase_price_waku_kengen = 0;
            $stdObj->arrow30                    = 0;
            $stdObj->arrow40                    = 0;
            $stdObj->arrow50                    = 0;
            $stdObj->arrow60                    = 0;
            $stdObj->arrow70                    = 0;
            $stdObj->arrow80                    = 0;
            $stdObj->arrow90                    = 0;
            $stdObj->edit_facility_medicine     = 0;

            $all_rec[] = $stdObj;
        }

        $this->end_log();

        return $all_rec;
    }

    // 本年度から過去３年を取得する(検索用)
    public function getSearchFiscalYearHistory() {
        $this->start_log();

        $today      = date('Y/m/d');
        $start_date = '04/01';

        $start_year = date('Y') . '/' . $start_date;

        if (strtotime($today) >= strtotime($start_year)) {
            // 2019/4/20 >= 2019/04/01 = 2019
            $year = date('Y');
        } else {
            // 2019/3/20 < 2019/04/01 = 2018
            $year = date('Y') - 1;
        }

        $tag_str = array();

        for ($i = 0; $i < 3; $i++) {
            $tag_str[] = $year . '/' . $start_date;
            $year--;
        }

        $this->end_log();

        return $tag_str;
    }

    // 採用品オプショナルキーの検索条件表示用
    public function getOptionalSearch($user_group_id) {
        $this->start_log();

        $sql = 'SELECT';

        for ($index = 1; $index <= 8; $index++) {
            $key_name     = 'optional_key' . $index;
            $label_name   = $key_name . '_label';
            $is_disp_name = $key_name . '_is_search_disp';

            if ($index > 1) {
                $sql .= ',';
            }
            $sql .= ' CASE WHEN ' . $is_disp_name . " = 'true' THEN " . $label_name . ' ELSE null END AS ' . $key_name;
        }
        $sql .= ' FROM';
        $sql .= '     user_groups ';
        $sql .= ' WHERE';
        $sql .= '    id = ' . $user_group_id;

        $all_rec = \DB::select($sql);

        $retList = array();

        if (!empty($all_rec)) {
            foreach ($all_rec[0] as $key => $value) {
                if (isset($value)) {
                    $retList[$key] = array(
                        'optional_key_label'      => $value,
                        'optional_is_search_disp' => true,
                    );
                }
            }
        }

        $this->end_log();

        return $retList;
    }

    // 採用品オプショナルキーのコンボボックス中身表示用
    public function getOptionalSearchListBox($user_group_id) {
        $this->start_log();

        $sql  = 'SELECT';
        $sql .= '    column_name';
        $sql .= '   ,value';
        $sql .= '   ,disp_order';
        $sql .= ' FROM';
        $sql .= '    v_optional_medicine_list';
        $sql .= ' WHERE';
        $sql .= "    user_group_id = '" . $user_group_id . "'";
        $sql .= ' ORDER BY';
        $sql .= '    column_name';
        $sql .= '   ,disp_order';

        $all_rec = \DB::select($sql);

        $retList = array();

        if (!empty($all_rec)) {
            foreach ($all_rec as $value) {
                $stdObj            = new \stdClass();
                $stdObj->value     =$value->value;
                $stdObj->disp_order=$value->disp_order;

                $retList[$value->column_name][] = $stdObj;
            }
        }

        $this->end_log();

        return $retList;
    }

    // 採用申請詳細画面の情報を取得
    public function getApplyDetailData($user, $medicine_id, $fm_id, $fp_id, $pa_id, $fp_hon_id) {
        $sql  = 'SELECT * FROM';
        $sql .= " f_price_adoptions_unique_select('" . $user->id . "', '" . $medicine_id . "', '" . $fm_id . "', '" . $fp_id . "', '" . $pa_id . "', '" . $fp_hon_id . "')";

        $result = $this->simple_execute_sql($sql);

        return $result[0];
    }

    // 一覧のデータのfacility_medicineを取得
    private function getFacilityPrice() {
        if (!$this->fm_parent && !$this->fm_own) {
            return;
        }

        $medicine_id = null;

        if ($this->fm_parent) {
            $medicine_id = $this->fm_parent;
        }

        if ($this->fm_own) {
            $medicine_id = $this->fm_own;
        }

        if (empty($this->fp_id)) {
            if ($medicine_id !== null) {
                $medicine = self::find($medicine_id);
                $fmModel  = new FacilityMedicine();

                if (isset($this->facility_id)) {
                    $fm = $fmModel->getData($this->facility_id, $medicine_id);

                    return ($fm) ? $fm->facilityPrice : null;
                }

                return;
            }
        } else {
            $fpModel = new FacilityPrice();
            $fp      = $fpModel->find($this->fp_id);

            return ($fp) ? $fp : null;
        }
    }
}
