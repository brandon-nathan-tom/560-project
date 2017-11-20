#!/run/current-system/profile/bin/guile
!#

(use-modules (sxml simple))
(use-modules (ice-9 pretty-print))

(define process-response
  (lambda (response)
	"doc for process-response"
	(display "processing response")
	(newline)
	(let ((root (car response)))
	 (display
	  (string-append
	   "root element:'" (symbol->string root) "'\n"))
	 (if (eq? root 'response)
		 ((lambda ()
			(display "found response - moving to result")
			(newline)
			(process-result response)))
		 (display "not response.\n"))
	 )
	(newline)
	))

(define process-result
  (lambda (result)
	"doc for process-result"
	(display "processing result")
	(newline)))

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

(newline)
(display "output:")
(newline)
(iterate-data sxml-data)
(newline)
