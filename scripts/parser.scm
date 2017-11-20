#!/run/current-system/profile/bin/guile
!#

(define process-response
  (lambda (response)
	"doc for process-response"
	(write "processing response")
	(newline)))

(define process-result
  (lambda (result)
	"doc for process-result"
	(write "processing result")
	(newline)))

(define iterate-data
  (lambda (data)
	"doc for iterate-data"
	(if (and (list? data) (< 0 (length data)))
		((lambda ()
		  (let ((this (car data)))
			(define qwer '())
			(if (list? this)
				(set! qwer (list-tail (car data) 1))
				(set! qwer (list-tail data 1))
				)
		  
			(if (eq? this 'project)
				((lambda ()
				   (write data)
				   (newline)))
				((lambda ()
				   ;; (write this)
				   ;; (newline)
				   (iterate-data qwer)))
				))
		  		  
		  (do ((i 1 (1+ i)))
		  	  ((and (list? data) (> i (- (length data) 1))))
		  	(iterate-data (list-ref data i))
		  	) ; do
		  )
		 )
		;; ((lambda ()
		;;    ;; (write '--------)
		;;    ;; (newline)
		;;    ))
		
		) ; (if (< 0 (length data))
	)
  )

(use-modules (sxml simple))
(use-modules (ice-9 pretty-print))

(define data-port (open-file "/home/niebie/sc/560-project/scripts/sample-data.xml" "r"))
(define sxml-data (xml->sxml data-port))

(newline)
(write 'output:)
(newline)
(iterate-data sxml-data)
(newline)
