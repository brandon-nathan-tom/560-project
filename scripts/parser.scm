#!/run/current-system/profile/bin/guile
!#

(use-modules (sxml simple))
(use-modules (ice-9 pretty-print))

(define verbose #t)
;(define verbose #f)

(define verbose-display
  (lambda (thing)
	(if verbose
		(display thing)
		)))

(define process-response
  (lambda (response)
	"doc for process-response"
	(verbose-display "processing response\n")
	(let ((root (car response)))
	 (verbose-display
	  (string-append
	   "root element:'" (symbol->string root) "'\n"))
	 (if (eq? root 'response)
		 ((lambda ()
			(verbose-display "found response - moving to result\n")
			(process-result response)))
		 (verbose-display "not response.\n"))
	 )
	))

(define process-result
  (lambda (result)
	"doc for process-result"
	(verbose-display "processing result\n")
	))

(define iterate-data
  (lambda (data)
	"doc for iterate-data"
	(do ((i 1 (1+ i)))
		((and (list? data) (> i (- (length data) 1))))
	  (let ((tmp (list-ref data i)))
		(process-response tmp)
		)
	  ) ; do
	)	
  )

(define data-port (open-file "/home/niebie/sc/560-project/scripts/sample-data.xml" "r"))
(define sxml-data (xml->sxml data-port))

(verbose-display "\noutput:\n")
(iterate-data sxml-data)

