package com.easync.easync;

import android.app.AlertDialog;
import android.content.DialogInterface;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.text.InputType;
import android.util.Log;
import android.view.View;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ScrollView;
import android.widget.TextView;

public class NewMeetingActivity extends AppCompatActivity {

    //Instantiate global variables
    //private String email_input = "";


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_new_meeting);
    }

    public void addEmailClick(View target){
        final AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Enter an Email");

        //Create Linear Layout for AlertDialog
        LinearLayout ll = new LinearLayout(this);
        ll.setOrientation(LinearLayout.VERTICAL);
        ll.setPadding(60,20,60,0);

        // Set up the input
        final EditText input = new EditText(this);
        // Specify the type of input expected; this, for example, sets the input as a password, and will mask the text
        input.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS);
        input.setHint("i.e. example@example.com");

        //Set view for dialog
        ll.addView(input);
        builder.setView(ll);

        // Set up the buttons
        builder.setPositiveButton("OK", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                String email_input = input.getText().toString();
                if (!(email_input.equals(""))) {
                    LinearLayout attendees = (LinearLayout) findViewById(R.id.attendees_list);
                    TextView someAttendee = new TextView(builder.getContext());
                    someAttendee.setPadding(20,0,20,0);
                    someAttendee.setTextSize(20);
                    someAttendee.setText(email_input);
                    attendees.addView(someAttendee);
                }
            }
        });
        builder.setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });
        AlertDialog alert = builder.create();
        alert.show();


        //After Alert
        Log.v("After Email Input", "After Alert.");
        /*
        if (!(email_input.equals(""))) {
            LinearLayout attendees = (LinearLayout) findViewById(R.id.attendees_list);
            TextView someAttendee = new TextView(this);
            someAttendee.setPadding(20,0,20,0);
            someAttendee.setTextSize(20);
            someAttendee.setText(email_input);
            attendees.addView(someAttendee);
        }

        //Reset input
        email_input = "";
        */
    }
}
