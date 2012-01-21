/*
 * $LynxId: LYUtils.c,v 1.187 2009/05/25 21:46:24 tom Exp $
 */
#include <HTUtils.h>
#include <HTTCP.h>
#include <HTParse.h>
#include <HTAccess.h>
#include <HTCJK.h>
#include <HTAlert.h>

#if defined(__MINGW32__)

extern int kbhit(void);		/* FIXME: use conio.h */

#undef UNIX

#elif defined(_WINDOWS)

#ifdef DONT_USE_GETTEXT
#undef gettext
#endif

#include <conio.h>

#ifdef DONT_USE_GETTEXT
#define gettext(s) s
#endif

#if !defined(kbhit) && defined(_WCONIO_DEFINED)
#define kbhit() _kbhit()	/* reasonably recent conio.h */
#endif

#endif /* __MINGW32__ */

#include <LYCurses.h>
#include <LYHistory.h>
#include <LYStrings.h>
#include <LYGlobalDefs.h>
#include <LYUtils.h>
#include <LYSignal.h>
#include <GridText.h>
#include <LYClean.h>
#include <LYCharSets.h>
#include <LYCharUtils.h>

#include <LYMainLoop.h>
#include <LYKeymap.h>

#ifdef __DJGPP__
#include <go32.h>
#include <sys/exceptn.h>
#endif /* __DJGPP__ */

#ifndef NO_GROUPS
#include <HTFile.h>
#endif

#ifdef _WINDOWS			/* 1998/04/30 (Thu) 19:04:25 */
#define GETPID()	(unsigned) (getpid() & 0xffff)
#else
#define GETPID()	(unsigned) getpid()
#endif /* _WINDOWS */

#ifdef FNAMES_8_3
#define PID_FMT "%04x"
#else
#define PID_FMT "%u"
#endif

#ifdef DJGPP_KEYHANDLER
#include <bios.h>
#endif /* DJGPP_KEYHANDLER */

#ifdef __EMX__
#  define BOOLEAN OS2_BOOLEAN	/* Conflicts, but is used */
#  undef HT_ERROR		/* Conflicts too */
#  define INCL_PM		/* I want some PM functions.. */
#  define INCL_DOSPROCESS	/* TIB PIB. */
#  include <os2.h>
#  undef BOOLEAN
#endif

#ifdef VMS
#include <descrip.h>
#include <libclidef.h>
#include <lib$routines.h>
#endif /* VMS */

#ifdef HAVE_UTMP
#include <pwd.h>
#ifdef UTMPX_FOR_UTMP
#include <utmpx.h>
#define utmp utmpx
#ifdef UTMPX_FILE
#ifdef UTMP_FILE
#undef UTMP_FILE
#endif /* UTMP_FILE */
#define UTMP_FILE UTMPX_FILE
#else
#ifdef __UTMPX_FILE
#define UTMP_FILE __UTMPX_FILE	/* at least in OS/390  S/390 -- gil -- 2100 */
#else
#ifndef UTMP_FILE
#define UTMP_FILE "/var/adm/utmpx"	/* Digital Unix 4.0 */
#endif
#endif
#endif /* UTMPX_FILE */
#else
#include <utmp.h>
#endif /* UTMPX_FOR_UTMP */
#endif /* HAVE_UTMP */

#ifdef NEED_PTEM_H
/* they neglected to define struct winsize in termios.h -- it's only in
 * termio.h and ptem.h (the former conflicts with other definitions).
 */
#include	<sys/stream.h>
#include	<sys/ptem.h>
#endif

#include <LYLeaks.h>

#ifdef USE_COLOR_STYLE
#include <AttrList.h>
#include <LYHash.h>
#include <LYStyle.h>
#endif

#ifdef SVR4_BSDSELECT
extern int BSDselect(int nfds, fd_set * readfds, fd_set * writefds,
		     fd_set * exceptfds, struct timeval *timeout);

#ifdef select
#undef select
#endif /* select */
#define select BSDselect
#ifdef SOCKS
#ifdef Rselect
#undef Rselect
#endif /* Rselect */
#define Rselect BSDselect
#endif /* SOCKS */
#endif /* SVR4_BSDSELECT */

#ifdef __DJGPP__
#undef select			/* defined to select_s in www_tcp.h */
#endif

#ifndef UTMP_FILE
#if defined(__FreeBSD__) || defined(__bsdi__)
#define UTMP_FILE _PATH_UTMP
#else
#define UTMP_FILE "/etc/utmp"
#endif /* __FreeBSD__ || __bsdi__ */
#endif /* !UTMP_FILE */

/*
 * experimental - make temporary filenames random to make the scheme less
 * obvious.  However, as noted by KW, there are instances (such as the
 * 'O'ption page, for which Lynx will store a temporary filename even when
 * it no longer applies, since it will reuse that filename at a later time.
 */
#ifdef EXP_RAND_TEMPNAME
#if defined(LYNX_RAND_MAX)
#define USE_RAND_TEMPNAME 1
#define MAX_TEMPNAME 10000
#ifndef BITS_PER_CHAR
#define BITS_PER_CHAR 8
#endif
#endif
#endif

#define COPY_COMMAND "%s %s %s"

static HTList *localhost_aliases = NULL;	/* Hosts to treat as local */
static char *HomeDir = NULL;	/* HOME directory */

HTList *sug_filenames = NULL;	/* Suggested filenames   */

/*
 * Maintain a list of all of the temp-files we create so that we can remove
 * them during the cleanup.
 */
typedef struct _LYTemp {
    struct _LYTemp *next;
    char *name;
    BOOLEAN outs;
    FILE *file;
} LY_TEMP;

static LY_TEMP *ly_temp;

static LY_TEMP *FindTempfileByName(const char *name)
{
    LY