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
}
