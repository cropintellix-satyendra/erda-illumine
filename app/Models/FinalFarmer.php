<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FinalFarmer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'final_farmers';
 	protected $guarded = [];
    // protected $fillable = ['district_id','district','taluka_id','taluka','village','farmer_name'];

    /**
     * Get the pipe data.
     */
    public function PlotPipeData(){
        return $this->hasMany(PipeInstallation::class, 'farmer_plot_uniqueid', 'farmer_plot_uniqueid');
    }

    public function season()
    {
        return $this->belongsTo(Season::class,'season','id');
    }

    public function farmer_farm_details()
    {
        return $this->hasOne(FarmerFarmDetails::class, 'farmer_uniqueId', 'farmer_uniqueId');
    }

    public function farmerConsentForm()
    {
        return $this->hasMany(FarmerConsentForm::class, 'farmer_uniqueId', 'farmer_uniqueId');
    }

    public function PlotImages(){
        return $this->hasMany(FinalFarmerPlotImage::class, 'farmer_id')->where('status', 'Approved');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'l2_apprv_reject_user_id', 'id');
    }

    /**
     * Get the pipe data.
     */
    public function AerationData(){
        return $this->hasMany(Aeration::class, 'farmer_plot_uniqueid', 'farmer_plot_uniqueid');
    }

    /**
     * Get the farmer plot associated with the approved plot.
     */
    public function organization(){
        return $this->hasOne(Company::class, 'id', 'organization_id');
    }

    /**
     * Get the farmer plot associated with the approved plot.
     */
    public function ApprvFarmerPlot(){
        return $this->hasOne(FarmerPlot::class, 'farmer_plot_uniqueid', 'farmer_plot_uniqueid');
    }

    public function FarmerPlot(){
        return $this->hasMany(FarmerPlot::class, 'farmer_plot_uniqueid', 'farmer_plot_uniqueid');
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function ApprvFarmerPlotImages(){
        return $this->hasMany(FinalFarmerPlotImage::class, 'farmer_unique_id', 'farmer_uniqueId');
    }

    /**
     * Get the user that approved or rejected the code.
     */
    public function UserApprovedRejected()
    {
        return $this->belongsTo(User::class, 'L1_aprv_recj_userid', 'id');
    }

    /**
     * Get the user that approved or rejected the code.
     */
    public function FinalUserApproved()
    {
        return $this->belongsTo(User::class, 'L2_appr_userid', 'id');
    }

    public function FinalUserRejected(){
        return $this->belongsTo(User::class, 'L2_reject_userid', 'id');
    }



    public function FinalUserApprovedRejected()
    {
        return $this->belongsTo(User::class,'L2_appr_userid','id');
    }
    /**
     * Get the farmer associated with the plot.
     */
    public function state(){
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function district(){
        return $this->hasOne(District::class, 'id', 'district_id');
    }

    public function taluka(){
        return $this->hasOne(Taluka::class, 'id', 'taluka_id');
    }

    public function panchayat(){
        return $this->hasOne(Panchayat::class, 'id', 'panchayat_id');
    }

    public function village(){
        return $this->hasOne(Village::class, 'id', 'village_id');
    }

    public function seasons(){
        return $this->hasOne(Season::class, 'id', 'season');
    }

    /**
     * Get the post that owns the comment.
     */
    public function users()
    {
        return $this->belongsTo(User::class, 'surveyor_id', 'id');
    }

    public function documents()
    {
        return $this->belongsTo(DocumentType::class, 'document_id', 'id');
    }

    
    /**
     * Get the farmer cropdata.
     */
    public function PlotCropData()
    {
        return $this->hasMany(FarmerCropdata::class, 'farmer_plot_uniqueid', 'farmer_plot_uniqueid');
    }

    /**
     * Get the farmer benefits data.
     */
    public function BenefitsData()
    {
        return $this->hasMany(FarmerBenefit::class, 'farmer_uniqueId', 'farmer_uniqueId');
    }

	public function surveyor()
     {
    return $this->hasOne(User::class, 'id', 'surveyor_id');
    }

}
