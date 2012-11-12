#include <stdio.h>
#include <stdlib.h>

#ifndef abs
 #define abs(x) ((x) < 0 ? -(x) : (x))
#endif


int pwm(int value) {
 FILE* f = fopen("/dev/pwm", "wb");
 fputc(value & 0xff, f);
 fputc((value >> 8) & 0xff, f);
 fclose(f);
}

int main() {
 int value = 0;
 int b;
 
 while(1) {
  b = abs(63 - 2*value);
  pwm(b * b);
  
  value = (value + 1) % 64;
  
  usleep(1000);
 }
}
