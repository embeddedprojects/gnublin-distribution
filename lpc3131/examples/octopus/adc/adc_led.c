#include <stdio.h>

#include <octopus.h>
 
#ifdef __MINGW_H
#include <windows.h>
#endif





#define PIN 10


int
main ()
{
  int r;
  struct octopus_context octopus;

  r = octopus_init (&octopus);
	printf("octopus_init returned %d\n", r);

  r = octopus_open (&octopus);
	printf("octopus_open returned %d\n", r);
  
  r = octopus_adc_init (&octopus, PIN);
	printf("octopus_adc_init returned %d\n", r);

  r = octopus_adc_ref (&octopus, 2);	//AVCC
	printf("octopus_adc_ref returned %d\n", r);

  int value;
  while (1)
	 {
		value = octopus_adc_get (&octopus, PIN);
		//printf ("Wert: %i, %i, %i, %i\n", eins, nichteins,value,((unsigned char)value & 0x01) ? 1 : 0);
		printf ("An PIN: %i\n", PIN);
		printf ("Wert: %i\n", value);
		//if((unsigned char)value & 0x01)
		// eins++; 
                //else nichteins++;
		//ocotpus_uart_write("Wert: %i\n", value);
		sleep (2);
	 }

  return 0;


}
