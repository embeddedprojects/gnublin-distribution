// LPC3131 Board v1.0 BoardControl
// (C) Benedikt Sauter


#define F_CPU 1000000L
 
#include <avr/io.h>         
#include <util/delay.h>         

#define EN_1V2 PB4
#define EN_1V8 PB3
#define EN_3V3 PB5
#define RESET  PB2 
 
int main (void) {            
 
   DDRB  = (1<<EN_1V2)|(1<<EN_1V8)|(1<<EN_3V3)|(1<<RESET);          

   // Reset to low
   PORTB = 0;
   _delay_ms(10);

   // enable power
   PORTB |= (1<<EN_1V2)|(1<<EN_1V8)|(1<<EN_3V3);

   // wait till power is ready
   _delay_ms(1000);
  
   // Reset to high 
   PORTB |= (1<<RESET);

 
   while(1) {                
   }                         
 
   return 0;                 
}
