/*
* Dieses Demoprogramm demonstriert eine einfaches Blinken der LED1 
*
*
*
*
*/

#include <stdio.h>
#include <octopus.h>

#define LED1 3
#define LED_ON 1
#define LED_OFF 0

int main (void)
{
  struct octopus_context octopus;
  char desc[64];

  if (!octopus_init (&octopus))
	 printf ("%s\n", octopus.error_str);

  if (octopus_open (&octopus) < 0)
	 {
		printf ("%s\n", octopus.error_str);
		exit (0);
	 }


  octopus_get_hwdesc (&octopus, desc);
  printf ("Device: %s\n", desc);
  printf ("\r\n");
  printf ("\r\n");
  printf ("LED1 Blink -DEMO- \r\n");
  printf ("########### \r\n");
  printf ("Die LED 1 sollte nun blinken.\r\n");
  printf ("Druecken Sie Strg und c, um die Demo zu beenden");
  printf ("\r\n");


  // Initialisierung der Octopusschnittstelle

  if (octopus_io_init (&octopus, LED1) < 0)
	 printf ("ERROR: %s\n", octopus.error_str);


  // Den PIN (LED1) als Ausgang setzen

  if (octopus_io_set_pin_direction_out (&octopus, LED1) < 0)
	 printf ("ERROR: %s\n", octopus.error_str);

  while(1)
  {
                // LED einshcalten
		if (octopus_io_set_pin (&octopus, LED1, LED_ON) < 0)
		  printf ("ERROR: %s\n", octopus.error_str);
		printf ("LED 1 an \r\n");
	
		sleep(1);

		//LED ausschalten
		if (octopus_io_set_pin (&octopus, LED1, LED_OFF) < 0)
		  printf ("ERROR: %s\n", octopus.error_str);
		printf ("LED 1 aus \r\n");

		sleep(1);
  }


  // 
  if (octopus_close (&octopus) < 1)
	 printf ("ERROR: %s\n", octopus.error_str);

  return 0;


}
