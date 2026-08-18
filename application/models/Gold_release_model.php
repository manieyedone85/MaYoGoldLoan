<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gold_release_model extends MY_Model
{
    protected $table = 'gold_releases';

    /** Mirrors GoldRelease::isReadyForRelease() -- all three checklist gates must be true. */
    public function is_ready_for_release(array $release)
    {
        return ! empty($release['id_proof_verified']) && ! empty($release['signature_captured']) && ! empty($release['photo_captured']);
    }

    /** Single release joined with loan/branch/jewellery-item details -- for the printable gold release receipt. */
    public function find_with_relations($id)
    {
        return $this->db->select('gold_releases.*, loans.loan_account_number, branches.name AS branch_name, jewellery_items.barcode, jewellery_items.purity_karat, jewellery_items.net_weight, jewellery_category_master.name AS category_name')
            ->from('gold_releases')
            ->join('loans', 'loans.id = gold_releases.loan_id', 'left')
            ->join('branches', 'branches.id = loans.branch_id', 'left')
            ->join('jewellery_items', 'jewellery_items.id = gold_releases.jewellery_item_id', 'left')
            ->join('jewellery_category_master', 'jewellery_category_master.id = jewellery_items.category_id', 'left')
            ->where('gold_releases.id', $id)
            ->get()
            ->row_array();
    }
}
