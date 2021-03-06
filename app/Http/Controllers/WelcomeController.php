<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Validator;
use Session;

class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
		if(!Session::has('user')){
			Session::put('user', uniqid());
		}
	}

	/**
	 * Show the application welcome screen to the user i.e the stream of questions sorted by votes.
	 *
	 * @return Response
	 */
	public function index()
	{
		//return Session::all();
		$questions = \App\Question::where('status','=','pending')->orderBy('votes','desc')->get();
		return view('users.stream')->with('questions',$questions);
	}

	/**
	 * viewing question archives.
	 *
	 * @return Response
	 */
	public function archives()
	{
		$questions = \App\Question::where('status','=','pending')->orderBy('votes','desc')->get();
		return view('users.archive')->with('questions',$questions);
	}

	/**
	 * creating an new question.
	 *
	 * @return Response
	 */
	public function getQuestion()
	{
		$questions = \App\Question::orderBy('created_at', 'DESC')->get()->toArray();
		return response()->json($questions);
	}

	public function getUpvote($id) {
		$question = \App\Question::find($id);
		// count if user has alreqdy voted for question
		$checkVoteStatus = \App\Check::where('session_id','=',Session::get('user'))->where('question_id','=',$id)->count();
		if ($question) {
			if($checkVoteStatus >= 1)
				// if user has voted return a failed upvote response
				return response()->json(['success' => false]);
			$question->votes += 1;
			$question->save();

			// saving session for checking users
			$checker = new \App\Check();
			$checker->question_id = $id;
			$checker->session_id = Session::get('user');
			$checker->save();

			return response()->json(['success' => true]);
		}
	}

	/**
	 * handling newly posted question.
	 *
	 * @return Response
	 */
	public function postQuestion(Request $request)
	{
		$validate = Validator::make($request->all(), [
	        'content' => 'required|max:200',
	        'from' => 'required',
	        'to' => 'required',
	    ]);

	    if($validate->passes()){
	    	$createQuestion = new \App\Question();
				$createQuestion->content = $request->input('content');
				$createQuestion->to = $request->input('to');
				$createQuestion->from = $request->input('from');
				$createQuestion->status = "pending";
				$createQuestion->votes = 1;
				$createQuestion->save();
				return redirect()->back();
		  }
	    else {
	    	return redirect()->back();
	    }
		return redirect()->back();;
	}

}
