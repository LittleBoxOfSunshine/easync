package com.easync.easync;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;

import org.w3c.dom.Text;

public class LoginActivity extends AppCompatActivity {

    //Initialize User Input Variables
    EditText email;
    EditText password;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        //Get views by id
        email = (EditText) findViewById(R.id.email_field);
        password = (EditText) findViewById(R.id.password_field);
        Button loginButton = (Button)findViewById(R.id.login_button);
        TextView registerAccount = (TextView)findViewById(R.id.register_text);

        //Listen to login button
        loginButton.setOnClickListener(new View.OnClickListener() {

            public void onClick(View v) {
                //Start a new intent
                Intent dashboardIntent = new Intent(getApplicationContext(), DashboardActivity.class);

                //Send data to the Registration Activity
                dashboardIntent.putExtra("email", email.getText().toString());
                dashboardIntent.putExtra("password", password.getText().toString());

                Log.e("Login Attempt", email.getText() + "." + password.getText());

                startActivity(dashboardIntent);
            }
        });

        //Listen to login button
        registerAccount.setOnClickListener(new View.OnClickListener() {

            public void onClick(View v) {
                //Start a new intent
                Intent registrationIntent = new Intent(getApplicationContext(), RegisterActivity.class);

                Log.v("LoginView", "Switch to register");

                startActivity(registrationIntent);
            }
        });

    }
}
