// DHT11
#include "DHT.h"
#define DHTPIN D6     // what digital pin we're connected to
#define DHTTYPE DHT11   // DHT 11
DHT dht(DHTPIN, DHTTYPE);

// SSD1306
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#define OLED_RESET 0
Adafruit_SSD1306 display(OLED_RESET);

// PMS7003
unsigned char  pms[32]; 
int PM1_0;
int PM2_5; 
int PM10;

// wifi
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
const char* ssid = "INPUT";
const char* password = "INPUT";
const char* id = "INPUT";

// web connect
int web_fail_count;
int web_success_count;
int web_last_httpcode;

void setup() 
{
  Serial.begin(9600);
  Serial.swap();
  dht.begin();
  display.begin(SSD1306_SWITCHCAPVCC, 0x3C);  // initialize with the I2C addr 0x3C (for the 128x32)

  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(WHITE);
  display.setCursor(0,1);
  display.print("WiFi Connecting..");
  display.display();
  WiFi.begin(ssid, password);
  int try_count = 0;
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(1000);
    try_count++;
    if (try_count > 20)
    {
      break;
    }
  }
  
  //delay(500);
}

void loop() 
{
  if (Serial.find(0x42))
  { 
    for (int j=1; j<32 ; j++)
    { 
      pms[j]=Serial.read(); 
    } 
    if (pms[1] == 0x4D)
    {
      PM1_0 = (pms[10] << 8) | pms[11]; 
      PM2_5 = (pms[12] << 8) | pms[13];
      PM10  = (pms[14] << 8) | pms[15];
    }
  }
  static unsigned long OledTimer = millis();
  static int tryCount = 28;
  if (millis() - OledTimer >= 1000)
  {
    OledTimer = millis();

    float h = dht.readHumidity();
    // Read temperature as Celsius (the default)
    float t = dht.readTemperature();
    // Read temperature as Fahrenheit (isFahrenheit = true)
    float f = dht.readTemperature(true);
  
    // Check if any reads failed and exit early (to try again).
    if (isnan(h) || isnan(t) || isnan(f)) 
    {
      //Serial.println("Failed to read from DHT sensor!");
      return;
    }
    
    display.clearDisplay();
  
    display.setTextSize(1);
    display.setTextColor(WHITE);
    display.setCursor(0,0);
    display.print("H: ");
    display.print(h, 1);
    display.print(" % ");
  
    //display.setTextSize(1);
    //display.setTextColor(WHITE);
    display.print(", T: ");
    display.print(t, 1);
    display.println(" C");
    
/*
    //display.setTextSize(1);
    //display.setTextColor(WHITE);
    display.print("PM1.0 : ");
    display.print(PM1_0);
    display.println(" ug/m3 ");
    display.print("PM2.5 : ");
    display.print(PM2_5);
    display.println(" ug/m3 ");
    display.print("PM10  : ");
    display.print(PM10);
    display.println(" ug/m3 ");
*/
    display.println("PM1.0, 2.0, 10 :ug/m3");
    display.print(PM1_0);
    display.print(",");   
    display.print(PM2_5);
    display.print(",");   
    display.print(PM10);
    display.println(" ");

    display.print(web_success_count);
    display.print("(");
    display.print(web_last_httpcode);
    display.print(")");
    display.print(web_fail_count);
    display.println(" ");

    display.display();
    
    if (++tryCount >= 30)
    {
      if (WiFi.status() == WL_CONNECTED) 
      { //Check WiFi connection status
        tryCount = 0;
        HTTPClient http;  //Declare an object of class HTTPClient

        String url = "http://barmi.dothome.co.kr/iot/record.php?";
        url += String("id=") + String(id);
        url += String("&H=") + String(h);
        url += String("&T=") + String(t);
        url += String("&PM1=") + String(PM1_0);
        url += String("&PM2=") + String(PM2_5);
        url += String("&PM3=") + String(PM10);
        
        http.begin(url);  //Specify request destination
        int httpCode = http.GET();                                                                  //Send the request
        web_last_httpcode = httpCode;
        web_success_count++;
        if (httpCode > 0) { //Check the returning code
     
          String payload = http.getString();   //Get the request response payload
          //Serial.println(payload);                     //Print the response payload
     
        }
     
        http.end();   //Close connection
      }
      else
      {
        web_fail_count++;
      }
    }
  }
}
