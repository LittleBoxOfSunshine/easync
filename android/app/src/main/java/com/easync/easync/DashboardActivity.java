package com.easync.easync;

import android.graphics.Color;
import android.graphics.Typeface;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.view.ViewGroup;
import android.view.animation.AccelerateInterpolator;
import android.view.animation.AlphaAnimation;
import android.view.animation.Animation;
import android.view.animation.AnimationSet;
import android.view.animation.DecelerateInterpolator;
import android.widget.RelativeLayout;
import android.widget.TextView;

public class DashboardActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard);

        // Initialize member TextViews so we can manipulate it later
        TextView newMeeting = (TextView) findViewById(R.id.new_meeting_text);
        TextView scheduled = (TextView) findViewById(R.id.scheduled_text);
        TextView groups = (TextView) findViewById(R.id.groups_text);

        Typeface type = Typeface.createFromAsset(getAssets(), "fonts/SansitaOne.ttf");
        newMeeting.setTypeface(type);
        scheduled.setTypeface(type);
        groups.setTypeface(type);
    }

    public void handleNewMeetingClick(View target){
        animateClick(target);
        //Do Things
    }

    public void handleScheduledClick(View target){
        animateClick(target);
        //Do Things
    }

    public void handleGroupsClick(View target){
        animateClick(target);
        //Do Things
    }

    //Animate the UI to notify the user of clicking
    private void animateClick(View target){

        //Fade the overlying animation from transparent to opaque
        Animation fade = new AlphaAnimation(0, 1);
        fade.setInterpolator(new DecelerateInterpolator());
        fade.setDuration(1000);

        //Loop once on a mirror of the animation
        fade.setRepeatMode(Animation.REVERSE);

        target.startAnimation(fade);
    }
}
