<?php

namespace App\Http\Controllers\Admin\settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Setting;

class TermsandconditionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = 'Terms & Conditions';
        $page_description = 'Some description for the page';
        $action ='form_editor_summernote';
        $Setting = Setting::where('id','1')->first();
        return view('admin.settings.terms-and-conditions.index', compact('Setting','page_title', 'page_description','action'));
	  }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function web_privacypolicy()
    {
        $page_title = 'Terms & Conditions';
        $page_description = 'Some description for the page';
        $action ='form_editor_summernote';
        $Setting = Setting::where('id','1')->first();
        return view('admin.settings.terms-and-conditions.web-termcond', compact('Setting','page_title', 'page_description','action'));
	  }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_privacypolicy(Request $request)
    {
      $Setting = Setting::where('id','1')->update(['web_privacypolicy'=>$request->web_privacypolicy,
      'app_privacypolicy'=>$request->web_privacypolicy ]);
      
      if(!$Setting){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      $page_title = 'Terms & Conditions';
        $page_description = 'Some description for the page';
        $action ='form_editor_summernote';
        $Setting = Setting::where('id','1')->first();
      return view('admin.settings.terms-and-conditions.web-termcond', compact('Setting','page_title', 'page_description','action'));
	  
    }

    public function store_term_condition(Request $request)
    {
      $Setting = Setting::where('id','1')->update(['app_termncond'=>$request->app_termncond]);
     
      if(!$Setting){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      $page_title = 'Terms & Conditions';
        $page_description = 'Some description for the page';
        $action ='form_editor_summernote';
        $Setting = Setting::where('id','1')->first();
      return view('admin.settings.terms-and-conditions.web-termcond', compact('Setting','page_title', 'page_description','action'));
	  
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      // dd($request->all());
      $Setting = Setting::where('id','1')->update(['terms_and_conditions'=>$request->terms_and_conditions]);
      if(!$Setting){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.terms-and-conditions.index').'#tnc_section')->with('success', 'Saved Successfully');
    }

  /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_carbon_credit(Request $request)
    {
      $Setting=Setting::where('id',1)->update(['carbon_credit'=>$request->carboncredit]);
      if(!$Setting){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect(route('admin.terms-and-conditions.index').'#carbon_section')->with('success', 'Saved Successfully');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function tnc_cquest()
    {
        $page_title = 'Terms & Conditions';
        $page_description = 'Some description for the page';
        $action ='form_editor_summernote';
        $Setting = Setting::where('id','1')->first();
        return view('admin.settings.terms-and-conditions.cquest-tnc', compact('Setting','page_title', 'page_description','action'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tnc_cquest_store(Request $request)
    {
      $Setting=Setting::where('id',1)->update(['cquest_tnc_cquest'=>$request->cquest_tnc_cquest]);
      if(!$Setting){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->back()->with('success', 'Saved Successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function privacy_policy_cquest_store(Request $request)
    {
      $Setting=Setting::where('id',1)->update(['cquest_privacypolicy_cquest'=>$request->cquest_privacypolicy_cquest]);
      if(!$Setting){
        return redirect()->back()->with('error', 'Something went wrong');
      }
      return redirect()->back()->with('success', 'Saved Successfully');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function web_tnc()
    {        
        $Setting = Setting::where('id','1')->first();
        return view('term-and-condition', compact('Setting'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function web_privacy_policy()
    {        
        $Setting = Setting::where('id','1')->first();
        return view('privacy-policy', compact('Setting'));
    }  

    public function web_privacy_policy_terms()
    {        
        $Setting = Setting::where('id','1')->first();
        return view('web-privacy-policy', compact('Setting'));
    }     
}
