<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;

use App\User;
use App\Calendar;
use App\Userskill;
use App\Skill;
use App\Event;
use App\Eventdate;
use App\Workspace;

class UserController extends Controller 
{
    /**
    * Apply jwt middleware to specific routes.
    * @param  void
    * @return void
    */
    public function __construct() 
    {
        $this->middleware('jwt.auth', [ 'only' => [
               'updateUser',
           'delete',
           'showUser',
           'user',
            'searchName',
           'search',
        //    'getSkills',
            // 'allSkills',
            'Organizers'
        ]]);
    }

    /**
     * Delete user from database.
     * @param userID
     * @return  Illuminate\Support\Facades\Response::class
    */
    public function delete($id)
    {
        // Check for Authorized user
        $role = Auth::user()->roleID;
        return Response::json($role);

        if ($role != 1) 
        {
            return Response::json(['error' => 'invalid credentials']);
        }
        // get user
        $user = User::find($id);
        // delete user account
        if($user->delete()) 
        {
          return Response::json(['success' => 'Account Deleted']);
        }
        // handle database error  
        return Response::json(['error' => 'Account could not be deleted']);
    }


    /**
     * Update user in database.
     * @param Illuminate\Support\Facades\Request::class
     * @return  Illuminate\Support\Facades\Response::class
     */
    public function updateUser(Request $request) 
    {
        //constants
        $userId = Auth::id();
        $rules = [
          'name' => 'nullable|string',
          'password' => 'nullable|string',
          'email' => 'nullable|string',
          'spaceID' => 'nullable|string',
          'company' => 'nullable|string',
          'website' => 'nullable|string',
          'bio' => 'nullable|string',
          'avatar' => 'nullable|string',
          'skills' => 'nullable|string',
          'phoneNumber' => 'nullable|string',
          'deleteSkills' => 'nullable|string',
        ];
        
        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ( $validator->fails() ) 
        {
            return Response::json(['error' => 'Invalid form input.']);
        }

        // TODO
        // $input = $request->all();
        // if (empty($input)) {
            
        // }

        // Form Input
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $company = $request->input('company');
        $website = $request->input('website');
        $phoneNumber = $request->input('phoneNumber');
        $bio = $request->input('bio');
        // User Skills
        $skills = explode(',',$request->input('skills'));
        $deleteSkills = explode(',',$request->input('deleteSkills'));
        // Avatar Input
        if (!empty($_FILES['avatar'])) 
        {
            // Check for file upload error
            if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) 
            {
                return Response::json([ "error" => "Upload failed with error code " . $_FILES['avatar']['error']]);
            }
            // checks for valid image upload
            $info = getimagesize($_FILES['avatar']['tmp_name']);

            if ($info === FALSE) 
            {
               return Response::json([ "error" => "Unable to determine image type of uploaded file" ]);
            }

            if ( ($info[2] !== IMAGETYPE_GIF) 
               && ($info[2] !== IMAGETYPE_JPEG) 
               && ($info[2] !== IMAGETYPE_PNG)) 
            {
                return Response::json([ "error" => "Not a gif/jpeg/png" ]);
            }

            // Get profile image input
            $avatar = $request->file('avatar');
        }
        // Ensure unique email
        if (!empty($email)) 
        {
            $check = User::where('email', $email)->first();
            if (!empty($check)) 
            {
                return Response::json(['error' => 'Email already in use']);
            }
        }

        $user = Auth::user();
        if (!empty($name)) $user->name = $name;
        if (!empty($email)) $user->email = $email;
        if (!empty($spaceID)) $user->spaceID = $spaceID;
        if (!empty($password)) $user->password = Hash::make($password);
        if (!empty($company)) $user->company = $company;
        if (!empty($website)) $user->website = $website;

        if ((!empty($phoneNumber)) 
            && (is_numeric($phoneNumber)) 
            && (count(str_split($phoneNumber)) == 10))
        {
            $user->phoneNumber = $phoneNumber;
        } 
        elseif (!empty($phoneNumber)) 
        {
            return Response::json([ 'error' => 'Invalid phone number' ]);
        }

    if (!empty($bio)) $user->bio = $bio;
      // Profile Picture
    if (!empty($avatar)) {
      $avatarName = $avatar->getClientOriginalName();
      $avatar->move('storage/avatar/', $avatarName);
      $user->avatar = $request->root().'/storage/avatar/'.$avatarName;
    }
    // Persist changes to database
    if (!$user->save()) {
       return Response::json(['error' => 'Account not created']);
    }

    // delete skills
    if (!empty($deleteSkills)) 
    {
        foreach ($deleteSkills as $key => $deleteSkill) 
        {
            Userskill::where('name', $deleteSkill)->where('userID', $userId)->delete();
        }
    }

    // check for and create new skill tags
    if (!empty($skills)) 
    {
        // create new Skills if not in database 
        foreach($skills as $key => $skill) 
        {
            // trim white space from input
            $trimmedSkill = trim($skill);
            $checkSkill = Skill::where('name', $trimmedSkill)->first();

            if (empty($checkSkill)) 
            {
                $newSkill = new Skill;
                $newSkill->name = $trimmedSkill;
                // Persist App\Skill to database
                if (!$newSkill->save()) 
                {
                    return Response::json([ 'error' => 'database error' ]);
                }     
            }
        }
    }

    // update App\Userskill;
    foreach ($skills as $key => $skill) 
    {
        // trim white space from input
        $trimmedSkill = trim($skill);
        // get current skill in iteration
        $skillTag = Skill::where('name', $trimmedSkill)->first();
        $checkUserSkill = Userskill::where('userID', $userId)
                                   ->where('skillID', $skillTag->id)
                                   ->first();;

        if (empty($checkUserSkill)) 
        {
            // Create new UserSkill
            $userSkill = new Userskill;
            $userSkill->userID = $userId;
            $userSkill->skillID = $skillTag->id;
            $userSkill->name = $skillTag->name;

            if (!$userSkill->save()) 
            {
                return Response::json([ 'error' => 'database error' ]);
            }
        }
    }
    return Response::json(['success' => 'User updated successfully.']);
    }

    /** 
     * Search Users by skill/spaceid
     * @param Illuminate\Support\Facades\Request
     * @return  Illuminate\Support\Facades\Response
    **/
    public function search(Request $request) 
    { 
        // url query params
        $query = $request->query('query');
        $tag = $request->query('tag');

        // handle skill tag button click
        if (!empty($tag)) 
        {
            $skills = Userskill::where('name', $tag)->select('userskills.userID')->get();
            if (count($skills) == 0) 
            {
                return Response::json([ 'error' => 'No users found with skill' ]);
            }

            $users = array();
            foreach ($skills as $key => $skill) 
            {
                $match = User::where('id', $skill['userID'])->where('searchOpt', false)->first();
                if (!empty($match)) 
                {
                    array_push($users, $match);
                }
            }
            if (!empty($users)) 
            {
                return Response::json($users);
            }
            else 
            {
                return Response::json([ 'error' => 'no user matched tag' ]);
            }
        }

        // handle search input query
        $users = User::where('name', 'LIKE', '%'.$query.'%')
                    ->Orwhere('bio', 'LIKE', '%'.$query.'%')
                    ->Orwhere('company', 'LIKE', '%'.$query.'%')
                    ->Orwhere('email', 'LIKE', '%'.$query.'%')
                    ->get();
        $skills = Userskill::where('name', 'LIKE', '%'.$query.'%')
                           ->select('userskills.userID')
                           ->get();

        // App\Skill match and App\User match
        if ( count($skills) != 0 && count($users) != 0) 
        {
            $res = array();
            foreach ($skills as $key => $skill) 
            {
                $match = User::where('id', $skill['userID'])
                             ->where('searchOpt', false)
                             ->first();

                if (!empty($match)) 
                {
                    array_push($res, $match);
                }
            }
            return ;
        } 

        // App\Skill match
        if ( count($users) == 0 && count($skills) != 0 ) 
        {
            $res = array();
            foreach ($skills as $key => $skill) 
            {
                $match = User::where('id', $skill['userID'])
                             ->where('searchOpt', false)
                             ->first();

                if (!empty($match)) 
                {
                    array_push($res, $match);
                }
            }
            return Response::json($res);
        }

        // App\User match
        if ( count($users) != 0 && count($skills) == 0 ) 
        {
            $res = array();
            foreach ($users as $user) 
            {
                if ($user->searchOpt == false) 
                {
                    array_push($res, $user);
                }
            }
            return Response::json($res);
        }
        return Response::json(['error' => 'nothing matched query']);
    }


  /**
   * Show logged in user.
   * @param void 
   * @return  Illuminate\Support\Facades\Response::class
  */
    public function showUser(Request $request) 
    {
         $user = Auth::user();
         $id = Auth::id();

        $skills = Userskill::where('userID', $id)
                           ->select('name')
                           ->get();

        $space = Workspace::where('id', $user->spaceID)
                          ->select('name')
                          ->first();

        $now = new DateTime();
        $events = Event::where('start', '>', $now->format('Y-m-d'))
                          ->select('title', 'id')
                          ->get();

        $attending = Calendar::where('userID', $id)->get();

        if (!empty($attending))
        {
            $upcoming = array();
            foreach ($attending as $attend)
            {
                $event = Event::find($attend->eventID);
                $eDate = new DateTime($event['start']);
                $diff = $now->diff($eDate);
                $formattedDiff = $diff->format('%R%a');

                if ((int)$formattedDiff > 0) 
                {
                    array_push($upcoming, 
                        [
                            "title" => $event->title,
                            "id" => $event->id 
                        ]
                    );
                }
            }
        }

       if (empty($user)) 
       {
          return Response::json([ 'error' => 'User does not exist' ]); 
       }
        return Response::json([
            'user' => $user,
            'skills' => !empty($skills) ? $skills : false,
            'space' => !empty($space) ? $space : false,
            'events' => !empty($events) ? $events : false,
            'upcoming' => !empty($upcoming) ? $upcoming : false,
        ]);   
    }


    public function allSkills() 
    {
        $skills = Skill::all();
        $skillsArray = [];
        foreach($skills as $skill) 
        {   array_push($skillsArray, [
                'label' => $skill->name,
                'value' => $skill->name,
                'id' => $skill->id
            ]);
        }
        return Response::json($skillsArray);
    }

    public function getSkills() 
    {
        $userskills = DB::table('userskills')
        ->select(DB::raw('COUNT(*) AS foo, skillID'))
        ->groupBY('skillID')
        ->orderBy('foo', 'desc')
        ->limit(6)
        ->get();

        $res = array();
        foreach ($userskills as $userskill) 
        {
            array_push($res, Skill::find($userskill->skillID));
        }
        return Response::json($res);
        // 'SELECT skillID, COUNT(*) AS foo FROM userskills GROUP BY skillID ORDER BY foo DESC LIMIT 6';
    }

    public function user($id) 
    {
        $user = User::find($id);

        $skills = Userskill::where('userID', $id)
                           ->select('name')
                           ->get();

        $space = Workspace::where('id', $user->spaceID)
                          ->select('name')
                          ->first();
        // $space = Workspace::find($user->spaceID)['name'];                          
        // return $space;
        $now = new DateTime();
        $events = Eventdate::where('start', '>', $now->format('Y-m-d'))
        ->select('eventID')
        ->get();
        
        $attending = Calendar::where('userID', $id)->get();

        if (!empty($attending))
        {

            $upcoming = array();
            foreach ($attending as $attend)
            {
                $event = Eventdate::find($attend->eventID);
                $eventTitle = Event::find($attend->eventID)['title'];
                $eDate = new DateTime($event->start);
                $diff = $now->diff($eDate);
                $formattedDiff = $diff->format('%R%a days');

                if ((int)$formattedDiff > 0) 
                {
                    array_push($upcoming, 
                        [
                            "title" => $eventTitle,
                            "id" => $event->id 
                        ]
                    );
                }
            }
        }

       if (empty($user) || $user->searchOpt) 
       {
          return Response::json([ 'error' => 'User does not exist' ]); 
       }
        return Response::json([
            'user' => $user,
            'skills' => !empty($skills) ? $skills : false,
            'space' => !empty($space) ? $space : false,
            'events' => !empty($events) ? $events : false,
            'upcoming' => !empty($upcoming) ? $upcoming : false,
        ]);   
    }

    public function Organizers() 
    {
        $organizers = User::all();
        $organizersArray = [];
        foreach($organizers as $organizer) 
        {
            array_push($organizersArray, [
                'label' => $organizer->email,
                'value' => $organizer->id,
                'avatar'=> $organizer->avatar,
                'name' => $organizer->name
            ]);
        }
        return Response::json($organizersArray);
    }

    public function usersFromSpace($spaceID) {
        $users = User::where('spaceID', $spaceID)->get();
        $usersArray = [];
        foreach($users as $user) {
            array_push($usersArray, [
                'label' => $user->name.'-'.$user->email,
                'value' => $user->email
            ]);
        }
        return Response::json($usersArray);
    }
}
