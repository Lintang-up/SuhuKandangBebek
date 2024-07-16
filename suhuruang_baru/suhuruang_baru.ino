#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <string>
#include <DHT.h>
#define DHTPIN 13
#define DHTTYPE DHT22
 
DHT dht(DHTPIN, DHTTYPE);

#include <ESP8266WiFi.h>
const char* ssid     = "sarah";
const char* password = "12341234"; 

const char* serverName = "http://suhu-ruangkandang.000webhostapp.com/api_suhu.php";

//int wifiStatus; 
float Temperature;
float Humidity;

#include <LiquidCrystal_I2C.h>
LiquidCrystal_I2C lcd(0x27,16,2); 

int kipas = 12;
int lamp = 14;

// variabel untuk menyimpan waktu terakhir pengiriman data
unsigned long previousMillis = 0;  
// interval waktu dalam milidetik (10 menit = 600000 milidetik)
const unsigned long interval = 10000;  

void setup(){
  Serial.begin(115200);
  delay(5000);
  Serial.println();  
  Serial.println("NodeMCU Aktif");
  Serial.println("Menghubungkan dengan Wi-Fi : ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
  delay(500);
  Serial.print(".");
  } 
  if(WiFi.status() == WL_CONNECTED){
    Serial.println("");
    Serial.println("ESP8266 terhubung dengan Wi-Fi!");  
    Serial.println("IP address : ");
    Serial.println(WiFi.localIP());  
  }
  
  Serial.println();    
  dht.begin();
  Serial.println("Mendeteksi suhu dan kelembaban : Aktif");
  
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0,0);
  lcd.print("Deteksi Suhu");
  lcd.setCursor(0,1);
  lcd.print("dan Kelembaban");
  delay(2000);
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("Suhu: ");
  lcd.setCursor(0,1);
  lcd.print("Lembab: ");

  pinMode(lamp, OUTPUT); 
  pinMode(kipas, OUTPUT); 
}

void loop() {
  // mendapatkan waktu saat ini
  unsigned long currentMillis = millis();  
  
    //membaca suhu dan kelembaban
  Humidity = dht.readHumidity();
  Temperature = dht.readTemperature();

  //memeriksa apakah ada pembacaan yang gagal
  if (isnan(Humidity) || isnan(Temperature)) {
    lcd.setCursor(6,0);
    lcd.print("Error    ");
    lcd.setCursor(8,1);
    lcd.print("Error    ");
    delay(2000); 
  }
  
  // Memeriksa apakah sudah mencapai interval waktu
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;  // menyimpan waktu terakhir pengiriman  
     
  WiFiClient client;
  HTTPClient http;
  
  String postData, Suhu_Data, Kelembaban_Data;
  
  //penyimpanan data sensor ke dalam variabel pengiriman data
  Suhu_Data = String(Temperature);
  Kelembaban_Data = String(Humidity);
  postData = "suhu=" +Suhu_Data + "&kelembaban=" +Kelembaban_Data;
    
  //menentukan URL hosting file API
  http.begin(client, serverName);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  
  //inisialisasi variabel HTTP request
  int httpCodepost = http.POST(postData); 
  String payload = http.getString();
  
  //proses pengiriman data ke API file
  Serial.println(httpCodepost);
  Serial.println(payload);
  http.end();
  }  
  
  //menampilkan suhu dan kelembaban ke serial
  Serial.print("\n Temperature: ");  
  Serial.print(Temperature);
  Serial.print("°C");
  Serial.print("\t Humidity:"); 
  Serial.print(Humidity);
  Serial.print("%\n");
  
  //menampilkan suhu dan kelembaban ke lcd
  lcd.setCursor(6,0);
  lcd.print(Temperature,1);
  lcd.print((char)223);
  lcd.print("C    ");
  lcd.setCursor(8,1);
  lcd.print(Humidity,0);
  lcd.print("%    ");

  if(Temperature <= 26.00 ){
    digitalWrite(lamp, HIGH);
    digitalWrite(kipas, LOW);
  } else if (Temperature > 26.00 ){
    digitalWrite(lamp, LOW);
    digitalWrite(kipas, HIGH);
  } else {
    digitalWrite(lamp, LOW);
    digitalWrite(kipas, LOW);  
  }

  delay(5000);

}
