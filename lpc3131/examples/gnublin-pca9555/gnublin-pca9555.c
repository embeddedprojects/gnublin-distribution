#include "gnublin-pca9555.h"

#define I2C_FILENAME "/dev/i2c-1"

int json_flag,brute_f;

int main(int argc, char **argv)
{
   int hflag, c, mflag, pflag, iflag, oflag, ivalue, mvalue, pvalue;
   int ovalue, i;

   
   pca9555 pca9555_tmp, *ppca9555_tmp;
   ppca9555_tmp = &pca9555_tmp; 
   
    
   while((c = getopt(argc,argv,"hlm:p:o:ijb")) != -1)
   {
      switch(c)
      {
         case 'h' : hflag = 1; break;
         case 'j' : json_flag = 1; break;
         case 'b' : brute_f = 1; break;
         case 'l' : show_list(ppca9555_tmp); break;
         case 'm' : mflag = 1; mvalue = atoi(optarg); break;
         case 'p' : pflag = 1; pvalue = atoi(optarg); break;
         case 'o' : oflag = 1; ovalue = atoi(optarg); break;
         case 'i' : iflag = 1; ivalue = atoi(optarg); break; 					
      }
   }
   

   if(hflag)
   {
              
   printf("This program was designed to easily interact with the pca9555 Portexpander.\n\n");             
   puts(
   "-h show this help\n"
   "-m<X> Specify the Portexpander number(0-7)\n"
   "-p<Y> Specify the portexpander pin (0-15)\n"
   "-o<value> Set pin as output with given <value>(0= low / 1 = high)\n"
   "-i ToDO\n"
   "-l list all Portexpanders connected to GNUBLIN\n"
   "-j Convert Output to json Format\n"
   "-b show output in raw format\n"            
   "\n\nExamples:\n\nSet pin 0 on module 0 high \ngnublin-pca9555 -m 0 -p 0 -o 1\n\n"
   "Set pin 0 of module 0 low \ngnublin-pca9555 -m 0 -p 0 -o 0\n\n"
   "Set pin 0 high on the first module \ngnublin-pca9555 -p 0 -o 1\n"
        );
        
                
        
      return 0;  
   }
   
if(mflag && pflag && oflag)
   {
      int buf_tmp[8], i;
      

      if (pca9555_init(ppca9555_tmp,I2C_FILENAME, 0,0) < 0)
      {
	if (json_flag == 1)
	   printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	else
	   printf("Failed to open i2c device\n"); 
	return -1;
      }
	 
      if (pca9555_show_addr(ppca9555_tmp, I2C_FILENAME, 0) < 0)
      {
	if (json_flag == 1)
	printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	else
	printf("Failed to open i2c device\n"); 
	return -1;
      }

      if(ppca9555_tmp->module[mvalue] != 0)
      {    
         if (pca9555_change_addr(ppca9555_tmp, ppca9555_tmp->module[mvalue]) < 0)
	 {
	      if (json_flag == 1)
	      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	      else
	      printf("Failed to open i2c device\n");
	      return -1;
	 }
	 
	 
	if (pvalue < 8) 
	{ 
		 //Use 0x06 and 0x02 as registers
		  if (pca9555_set_port_dir(ppca9555_tmp, (unsigned char)pvalue, 0x06, 0) < 0)
		  {
			if (json_flag == 1)
			printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
			else
			printf("Failed to open i2c device\n"); 
			return -1;
		  }
	  
		  if (pca9555_set_port_val(ppca9555_tmp,(unsigned char)pvalue, 0x02, ovalue) < 0 )
		  {
		      if (json_flag == 1)
		      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
		      else
		      printf("Failed to open i2c device\n");
		      return -1;
		  } 
		}   
	else
	{ 
		 //Use 0x07 and 0x03 as registers
		  if (pca9555_set_port_dir(ppca9555_tmp, (unsigned char)pvalue, 0x07, 0) < 0)
		  {
			if (json_flag == 1)
			printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
			else
			printf("Failed to open i2c device\n"); 
			return -1;
		  }
		if (pca9555_set_port_val(ppca9555_tmp,(unsigned char)pvalue, 0x03, ovalue) < 0 )
		{
		      if (json_flag == 1)
		      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
		      else
		      printf("Failed to open i2c device\n");
		      return -1;
		} 
	}	
	


      }
      else
      {
	if (json_flag == 1)
	      printf("{\"error_msg\" : \"No module connected\",\"result\" : \"-1\"}\n");
	else
	      printf("No module connected\n");
	return -1;     
      }
      
      
      if (json_flag == 1)
      printf("{\"result\" : \"0\"}\n");
      else
      printf("Operation successfully done.\n"); 
      return 0;
      
   }
   
  
if(pflag && oflag)
   {
      if (pca9555_init(ppca9555_tmp,I2C_FILENAME, 0,0) < 0)
      {
	if (json_flag == 1)
	printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	else
	printf("Failed to open i2c device\n"); 
	return -1;
      }
      
      
      if (pca9555_show_addr(ppca9555_tmp, I2C_FILENAME, 0) < 0)
      {
	if (json_flag == 1)
	printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	else
	printf("Failed to open i2c device\n"); 
	return -1;
      }
      
      
      if(ppca9555_tmp->module[0] != 0)
      {
	  
        if (pca9555_change_addr(ppca9555_tmp, ppca9555_tmp->module[0]) < 0)
	 {
	      if (json_flag == 1)
	      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	      else
	      printf("Failed to open i2c device\n"); 
	      return -1;
	 }
	 
	if (pvalue < 8) 
	{ 
		 //Use 0x06 and 0x02 as registers
		if (pca9555_set_port_dir(ppca9555_tmp, (unsigned char)pvalue, 0x06, 0) < 0)
		{
		      if (json_flag == 1)
		      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
		      else
		      printf("Failed to open i2c device\n");
		      return -1;
		}
	 
		if (pca9555_set_port_val(ppca9555_tmp,(unsigned char)pvalue, 0x02, ovalue) < 0)
		{
		      if (json_flag == 1)
		      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
		      else
		      printf("Failed to open i2c device\n"); 
		      return -1;
		}
	} 
	else
	{ 	
		 //Use 0x07 and 0x03 as registers
		if (pca9555_set_port_dir(ppca9555_tmp, (unsigned char)pvalue, 0x07, 0) < 0)
		{
		      if (json_flag == 1)
		      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
		      else
		      printf("Failed to open i2c device\n");
		      return -1;
		}
		
		if (pca9555_set_port_val(ppca9555_tmp,(unsigned char)pvalue, 0x03, ovalue) < 0)
		{
		      if (json_flag == 1)
		      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
		      else
		      printf("Failed to open i2c device\n"); 
		      return -1;
		}
	} 
	  
	  
      }
      else
      {
	if (json_flag == 1)
	      printf("{\"error_msg\" : \"No module connected\",\"result\" : \"-1\"}\n");
	else
	      printf("No module connected\n");
	return -1;     
      }
      
      
      if (json_flag == 1)
      printf("{\"result\" : \"0\"}\n");
      else
      printf("Operation successfully done.\n"); 
      
      return 0;
   
   }
 
#if 0
   if(iflag && mflag)
   {
      unsigned char port_tmp=0x02;
      if (pca9555_init(ppca9555_tmp,I2C_FILENAME, 0,0) < 0)
      {
	if (json_flag == 1)
	printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	else
	printf("Failed to open i2c device\n");
	return -1;
      }
      
      
      if (pca9555_show_addr(ppca9555_tmp, I2C_FILENAME, 0) < 0)
      {
	if (json_flag == 1)
	printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	else
	printf("Failed to open i2c device\n"); 
	return -1;
      }
      
      
      if(ppca9555_tmp->module[mvalue] != 0)
      {    
         if (pca9555_change_addr(ppca9555_tmp, ppca9555_tmp->module[mvalue]) < 0)
	 {
	      if (json_flag == 1)
	      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
	      else
	      printf("Failed to open i2c device\n"); 
	      return -1;
	 }
	 
	if (pvalue < 8) 
	{ 
		 //Use 0x06 and 0x02 as registers
		if (pca9555_set_port_dir(ppca9555_tmp, (unsigned char)pvalue, 0x06, 1) < 0)
		{
		      if (json_flag == 1)
		      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
		      else
		      printf("Failed to open i2c device\n"); 
		      return -1;
		}
	 
		if (pca9555_get_pin_val(ppca9555_tmp,(unsigned char)pvalue, 0x02 ) == 0)
		{
		      if (json_flag == 1)
		      printf("{\"value\" : \"0\",\"result\" : \"0\"}\n");
		      else
		      printf("Value = 0\n"); 
		      return -1;
		}
		else
		{
		    if (json_flag == 1)
		      printf("{\"value\" : \"1\",\"result\" : \"0\"}\n");
		    else
		      printf("Value = 1\n"); 
		    return -1;
		}
	}
	 else
	{ 
		//Use 0x07 and 0x03 as registers
		if (pca9555_set_port_dir(ppca9555_tmp, (unsigned char)pvalue, 0x07, 1) < 0)
		{
		      if (json_flag == 1)
		      printf("{\"error_msg\" : \"Failed to open i2c device\",\"result\" : \"-1\"}\n");
		      else
		      printf("Failed to open i2c device\n"); 
		      return -1;
		}
	 
		if (pca9555_get_pin_val(ppca9555_tmp,(unsigned char)pvalue, 0x03 ) == 0)
		{
		      if (json_flag == 1)
		      printf("{\"value\" : \"0\",\"result\" : \"0\"}\n");
		      else
		      printf("Value = 0\n"); 
		      return -1;
		}
		else
		{
		    if (json_flag == 1)
		      printf("{\"value\" : \"1\",\"result\" : \"0\"}\n");
		    else
		      printf("Value = 1\n"); 
		    return -1;
		}   
	}
         
      }
      
      else
      {
	if (json_flag == 1)
	      printf("{\"error_msg\" : \"No module connected\",\"result\" : \"-1\"}\n");
	else
	      printf("No module connected\n");
	return -1;     
      }
      
      
      if (json_flag == 1)
      printf("{\"result\" : \"0\"}\n");
      else
      printf("wert ist %02x\n",port_tmp);
      
      return 0;
   }
#endif 
}


void show_list(pca9555 *pca9555_tmp){
    
    if (pca9555_init(pca9555_tmp,I2C_FILENAME, 0, 0) < 0)
    {
	if (json_flag == 1)
	printf("{\"error_msg\" : \"Failed to open i2c device \",\"result\" : \"-1\"}\n");
	else
	printf("Failed to open i2c device\n"); 
    }
    
    if (pca9555_show_addr(pca9555_tmp, I2C_FILENAME, 1) < 0)
    {
	if (json_flag == 1)
	printf("{\"error_msg\" : \"Failed to open i2c device \",\"result\" : \"-1\"}\n");
	else
	printf("Failed to open i2c device\n"); 
    }

}
   
